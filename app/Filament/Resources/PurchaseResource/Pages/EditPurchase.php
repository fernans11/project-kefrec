<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPurchase extends EditRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return PurchaseResource::normalizePurchaseData($data);
    }

    protected function afterSave(): void
    {
        $this->record->refresh()->load('items.ingredient');
        $this->record->recalculateTotalsFromItems();
        $this->record->applyReceiptIfNeeded();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Hapus')
                ->visible(fn (): bool => $this->record->status !== 'received' && blank($this->record->received_at)),
        ];
    }
}
