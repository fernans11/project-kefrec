<?php

namespace App\Filament\Resources\PurchaseReturnResource\Pages;

use App\Filament\Resources\PurchaseReturnResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchaseReturn extends CreateRecord
{
    protected static string $resource = PurchaseReturnResource::class;

    protected function afterCreate(): void
    {
        $this->record->applyReturnIfNeeded();
        $this->syncCashflow();
    }

    private function syncCashflow(): void
    {
        if ($this->record->status !== 'processed') {
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
