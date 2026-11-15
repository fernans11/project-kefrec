<?php

namespace App\Filament\Resources\OwnerUserResource\Pages;

use App\Filament\Resources\OwnerUserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOwnerUser extends CreateRecord
{
    protected static string $resource = OwnerUserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['usertype'] = 'owner';

        return $data;
    }
}
