<?php

namespace App\Models;

use App\Support\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Purchase extends Model
{
    protected $fillable = [
        'invoice_no',
        'supplier_id',
        'purchased_at',
        'status',
        'subtotal',
        'tax',
        'total',
        'received_at',
        'notes',
    ];

    protected $casts = [
        'purchased_at' => 'date',
        'received_at' => 'datetime',
        'subtotal' => 'integer',
        'tax' => 'integer',
        'total' => 'integer',
    ];

    public static function allowedStatusTransitions(): array
    {
        return [
            'draft' => ['received', 'cancelled'],
            'received' => [],
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
        static::updating(function (Purchase $purchase) {
            if (! $purchase->isDirty('status')) {
                return;
            }

            $next = (string) $purchase->status;
            $current = (string) $purchase->getOriginal('status');

            if ($current === '') {
                return;
            }

            if (! self::canTransition($current, $next)) {
                throw ValidationException::withMessages([
                    'status' => 'Perubahan status pembelian tidak valid.',
                ]);
            }
        });
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public static function generateInvoiceNo(): string
    {
        $date = now()->format('ymd');

        do {
            $rand = strtoupper(Str::random(6));
            $invoice = "PO-{$date}-{$rand}";
        } while (self::where('invoice_no', $invoice)->exists());

        return $invoice;
    }

    public function applyReceiptIfNeeded(): void
    {
        if ($this->status !== 'received') {
            return;
        }

        if ($this->received_at) {
            return;
        }

        $this->loadMissing('items.ingredient');

        DB::transaction(function () {
            $fresh = $this->newQuery()->lockForUpdate()->find($this->id);
            if (!$fresh || $fresh->received_at) {
                return;
            }

            foreach ($this->items as $item) {
                $ingredient = $item->ingredient;
                if (!$ingredient) {
                    continue;
                }

                $qty = (float) ($item->qty ?? 0);
                if ($qty <= 0) {
                    continue;
                }

                $ingredient->stock = (float) $ingredient->stock + $qty;
                $ingredient->save();
            }

            $fresh->received_at = now();
            $fresh->saveQuietly();
        });

        $this->recordCashflowIfNeeded();

        AuditLogger::log(
            'purchase.received',
            $this,
            'Pembelian diterima',
            [
                'invoice_no' => $this->invoice_no,
                'total' => $this->total,
            ]
        );
    }

    private function recordCashflowIfNeeded(): void
    {
        $note = 'Pembelian ' . $this->invoice_no;
        $exists = Cashflow::where('source', 'purchase')
            ->where('notes', $note)
            ->exists();

        if ($exists) {
            return;
        }

        Cashflow::create([
            'date' => now(),
            'type' => 'out',
            'category' => 'Pembelian Bahan Baku',
            'amount' => $this->total,
            'source' => 'purchase',
            'notes' => $note,
        ]);
    }
}
