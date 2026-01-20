<?php

namespace App\Models;

use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Transaction extends Model
{
    protected $fillable = [
        'invoice_no',
        'customer_id',
        'cashier_id',
        'subtotal',
        'discount',
        'tax',
        'total',
        'payment_method',
        'paid_amount',
        'change_amount',
        'status',
        'stock_deducted_at',
    ];

    protected $casts = [
        'subtotal' => 'integer',
        'discount' => 'integer',
        'tax' => 'integer',
        'total' => 'integer',
        'paid_amount' => 'integer',
        'change_amount' => 'integer',
        'stock_deducted_at' => 'datetime',
    ];

    public static function allowedStatusTransitions(): array
    {
        return [
            'draft' => ['pending_cashier', 'paid', 'cancelled'],
            'pending_cashier' => ['processing', 'cancelled'],
            'paid' => ['processing', 'cancelled'],
            'processing' => ['ready', 'cancelled'],
            'ready' => ['completed'],
            'completed' => [],
            'cancelled' => [],
        ];
    }

    public static function canTransition(string $fromStatus, string $nextStatus): bool
    {
        if ($fromStatus === $nextStatus) {
            return true;
        }

        $allowed = self::allowedStatusTransitions()[$fromStatus] ?? [];
        return in_array($nextStatus, $allowed, true);
    }

    public function canTransitionTo(string $nextStatus): bool
    {
        return self::canTransition((string) $this->status, $nextStatus);
    }

    protected static function booted(): void
    {
        static::updating(function (Transaction $transaction) {
            if (! $transaction->isDirty('status')) {
                return;
            }

            $next = (string) $transaction->status;
            $current = (string) $transaction->getOriginal('status');

            if ($current === '') {
                return;
            }

            if (! self::canTransition($current, $next)) {
                throw ValidationException::withMessages([
                    'status' => 'Perubahan status transaksi tidak valid.',
                ]);
            }
        });

        static::created(function (Transaction $transaction) {
            AuditLogger::log(
                'transaction.created',
                $transaction,
                'Transaksi dibuat',
                ['status' => $transaction->status]
            );
        });

        static::updated(function (Transaction $transaction) {
            if (! $transaction->wasChanged('status')) {
                return;
            }

            AuditLogger::log(
                'transaction.status_changed',
                $transaction,
                'Status transaksi diperbarui',
                [
                    'from' => $transaction->getOriginal('status'),
                    'to' => $transaction->status,
                ]
            );
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function deductStockIfNeeded(): void
    {
        if ($this->stock_deducted_at) {
            return;
        }

        $this->loadMissing('items.product.ingredients');

        DB::transaction(function () {
            $fresh = $this->newQuery()->lockForUpdate()->find($this->id);
            if (!$fresh || $fresh->stock_deducted_at) {
                return;
            }

            foreach ($this->items as $item) {
                $product = $item->product;
                if (!$product) {
                    continue;
                }

                $qty = (float) ($item->qty ?? 0);
                if ($qty <= 0) {
                    continue;
                }

                $ingredients = $product->ingredients;

                if ($ingredients->count() > 0) {
                    foreach ($ingredients as $ingredient) {
                        $perUnit = (float) ($ingredient->pivot->qty ?? 0);
                        $deduct = round($qty * $perUnit, 2);
                        if ($deduct <= 0) {
                            continue;
                        }

                        $current = (float) $ingredient->stock;
                        $ingredient->stock = max(0, $current - $deduct);
                        $ingredient->save();
                    }
                } else {
                    if (isset($product->track_stock) && $product->track_stock) {
                        $current = (int) ($product->stock ?? 0);
                        $product->stock = max(0, $current - (int) $qty);
                        $product->save();
                    }
                }
            }

            $fresh->stock_deducted_at = now();
            $fresh->save();
        });
    }

    public function recordCashflowIfNeeded(): void
    {
        if ($this->status !== 'completed') {
            return;
        }

        $note = 'Penjualan ' . $this->invoice_no;
        $exists = Cashflow::where('source', 'sales')
            ->where('notes', $note)
            ->exists();

        if ($exists) {
            return;
        }

        Cashflow::create([
            'date' => now(),
            'type' => 'in',
            'category' => 'Penjualan',
            'amount' => $this->total,
            'source' => 'sales',
            'notes' => $note,
        ]);
    }
}
