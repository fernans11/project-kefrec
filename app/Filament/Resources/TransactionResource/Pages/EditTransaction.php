<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Resources\Pages\EditRecord;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Jaga-jaga cashier tetap user login (opsional)
        $data['cashier_id'] = $data['cashier_id'] ?? auth()->id();
        return $data;
    }
}
