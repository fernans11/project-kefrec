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
        static::deleting(function (Purchase $purchase) {
            if ($purchase->status === 'received' || $purchase->received_at) {
                throw ValidationException::withMessages([
                    'status' => 'Pembelian yang sudah diterima tidak boleh dihapus karena stok dan cashflow sudah diproses. Gunakan Retur Pembelian untuk koreksi stok.',
                ]);
            }
        });

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
            $this->refresh()->load(['supplier', 'items.ingredient']);
            $this->recordCashflowIfNeeded();

            return;
        }

        DB::transaction(function () {
            $fresh = $this->newQuery()->lockForUpdate()->find($this->id);
            if (!$fresh || $fresh->received_at) {
                return;
            }

            $fresh->load('items.ingredient');

            foreach ($fresh->items as $item) {
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

        $this->refresh()->load(['supplier', 'items.ingredient']);
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

    public function recalculateTotalsFromItems(): void
    {
        $this->loadMissing('items');

        $subtotal = 0;

        foreach ($this->items as $item) {
            $itemSubtotal = (int) round((float) ($item->qty ?? 0) * (int) ($item->price ?? 0), 0);
            $subtotal += $itemSubtotal;

            if ((int) $item->subtotal !== $itemSubtotal) {
                $item->subtotal = $itemSubtotal;
                $item->saveQuietly();
            }
        }

        $tax = max(0, (int) ($this->tax ?? 0));

        $this->forceFill([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => max(0, $subtotal + $tax),
        ])->saveQuietly();

        $this->refresh()->load(['supplier', 'items.ingredient']);
    }

    private function recordCashflowIfNeeded(): void
    {
        $note = $this->cashflowNote();
        $cashflow = Cashflow::where('source', 'purchase')
            ->where('notes', 'like', 'Pembelian ' . $this->invoice_no . '%')
            ->first();

        if ($cashflow) {
            $cashflow->update([
                'type' => 'out',
                'category' => 'Pembelian Bahan Baku',
                'amount' => $this->total,
                'notes' => $note,
            ]);

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

    private function cashflowNote(): string
    {
        $this->loadMissing(['supplier', 'items.ingredient']);

        $supplierName = $this->supplier?->name ?? 'Tanpa supplier';
        $items = $this->items
            ->map(function ($item) {
                $name = $item->ingredient?->name ?? 'Bahan baku tidak ditemukan';
                $qty = rtrim(rtrim(number_format((float) $item->qty, 2, '.', ''), '0'), '.');
                $subtotal = number_format((int) $item->subtotal, 0, ',', '.');

                return "{$name} {$qty} x Rp {$subtotal}";
            })
            ->implode('; ');

        if ($items === '') {
            $items = 'Tidak ada item';
        }

        return "Pembelian {$this->invoice_no} dari {$supplierName}: {$items}";
    }
}
