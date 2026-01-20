<?php

namespace App\Filament\Resources\PurchaseReturnResource\Pages;

use App\Filament\Resources\PurchaseReturnResource;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseReturn extends EditRecord
{
    protected static string $resource = PurchaseReturnResource::class;

    protected function afterSave(): void
    {
        $this->record->applyReturnIfNeeded();
        $this->syncCashflow();
    }

    private function syncCashflow(): void
    {
        if ($this->record->status !== 'processed') {
            return;
        }

        $exists = \App\Models\Cashflow::where('source', 'purchase_return')
            ->where('notes', 'Retur pembelian ' . $this->record->return_no)
            ->exists();

        if ($exists) {
            return;
        }

        \App\Models\Cashflow::create([
            'date' => now(),
            'type' => 'in',
            'category' => 'Retur Pembelian',
            'amount' => $this->record->total,
            'source' => 'purchase_return',
            'notes' => 'Retur pembelian ' . $this->record->return_no,
        ]);
    }
}
