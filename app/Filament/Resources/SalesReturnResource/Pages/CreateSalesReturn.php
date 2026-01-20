<?php

namespace App\Filament\Resources\SalesReturnResource\Pages;

use App\Filament\Resources\SalesReturnResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSalesReturn extends CreateRecord
{
    protected static string $resource = SalesReturnResource::class;

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
            'type' => 'out',
            'category' => 'Retur Penjualan',
            'amount' => $this->record->total,
            'source' => 'sales_return',
            'notes' => 'Retur penjualan ' . $this->record->return_no,
        ]);
    }
}
