<?php

namespace App\Models;

use App\Support\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SalesReturn extends Model
{
    protected $fillable = [
        'return_no',
        'transaction_id',
        'customer_id',
        'status',
        'subtotal',
        'tax',
        'total',
        'processed_at',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'integer',
        'tax' => 'integer',
        'total' => 'integer',
        'processed_at' => 'datetime',
    ];

    public static function allowedStatusTransitions(): array
    {
        return [
            'draft' => ['processed', 'cancelled'],
            'processed' => [],
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
        static::updating(function (SalesReturn $salesReturn) {
            if (! $salesReturn->isDirty('status')) {
                return;
            }

            $next = (string) $salesReturn->status;
            $current = (string) $salesReturn->getOriginal('status');

            if ($current === '') {
                return;
            }

            if (! self::canTransition($current, $next)) {
                throw ValidationException::withMessages([
                    'status' => 'Perubahan status retur penjualan tidak valid.',
                ]);
            }
        });
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesReturnItem::class);
    }

    public static function generateReturnNo(): string
    {
        $date = now()->format('ymd');

        do {
            $rand = strtoupper(Str::random(6));
            $no = "RET-{$date}-{$rand}";
        } while (self::where('return_no', $no)->exists());

        return $no;
    }

    public function applyReturnIfNeeded(): void
    {
        if ($this->status !== 'processed') {
            return;
        }

        if ($this->processed_at) {
            return;
        }

        $this->loadMissing('items.product.ingredients');

        DB::transaction(function () {
            $fresh = $this->newQuery()->lockForUpdate()->find($this->id);
            if (!$fresh || $fresh->processed_at) {
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
                        $add = round($qty * $perUnit, 2);
                        if ($add <= 0) {
                            continue;
                        }

                        $ingredient->stock = (float) $ingredient->stock + $add;
                        $ingredient->save();
                    }
                } else {
                    if (isset($product->track_stock) && $product->track_stock) {
                        $product->stock = (int) ($product->stock ?? 0) + (int) $qty;
                        $product->save();
                    }
                }
            }

            $fresh->processed_at = now();
            $fresh->saveQuietly();
        });

        AuditLogger::log(
            'sales_return.processed',
            $this,
            'Retur penjualan diproses',
            [
                'return_no' => $this->return_no,
                'total' => $this->total,
            ]
        );
    }
}
