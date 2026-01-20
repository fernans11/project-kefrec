<?php

namespace App\Filament\Resources\SalesReturnResource\Pages;

use App\Filament\Resources\SalesReturnResource;
use Filament\Resources\Pages\EditRecord;

class EditSalesReturn extends EditRecord
{
    protected static string $resource = SalesReturnResource::class;

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

        $exists = \App\Models\Cashflow::where('source', 'sales_return')
            ->where('notes', 'Retur penjualan ' . $this->record->return_no)
            ->exists();

        if ($exists) {
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
