<?php

namespace App\Models;

use App\Support\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PurchaseReturn extends Model
{
    protected $fillable = [
        'return_no',
        'purchase_id',
        'supplier_id',
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
        static::updating(function (PurchaseReturn $purchaseReturn) {
            if (! $purchaseReturn->isDirty('status')) {
                return;
            }

            $next = (string) $purchaseReturn->status;
            $current = (string) $purchaseReturn->getOriginal('status');

            if ($current === '') {
                return;
            }

            if (! self::canTransition($current, $next)) {
                throw ValidationException::withMessages([
                    'status' => 'Perubahan status retur pembelian tidak valid.',
                ]);
            }
        });
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public static function generateReturnNo(): string
    {
        $date = now()->format('ymd');

        do {
            $rand = strtoupper(Str::random(6));
            $no = "RET-PO-{$date}-{$rand}";
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

        $this->loadMissing('items.ingredient');

        DB::transaction(function () {
            $fresh = $this->newQuery()->lockForUpdate()->find($this->id);
            if (!$fresh || $fresh->processed_at) {
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

                $ingredient->stock = max(0, (float) $ingredient->stock - $qty);
                $ingredient->save();
            }

            $fresh->processed_at = now();
            $fresh->saveQuietly();
        });

        AuditLogger::log(
            'purchase_return.processed',
            $this,
            'Retur pembelian diproses',
            [
                'return_no' => $this->return_no,
                'total' => $this->total,
            ]
        );
    }
}
