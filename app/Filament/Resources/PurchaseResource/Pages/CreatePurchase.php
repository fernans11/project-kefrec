<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return PurchaseResource::normalizePurchaseData($data);
    }

    protected function afterCreate(): void
    {
        $this->record->refresh()->load('items.ingredient');
        $this->record->recalculateTotalsFromItems();
        $this->record->applyReceiptIfNeeded();
    }
}
