<?php

namespace App\Filament\Resources\KabKotaResource\Pages;

use App\Filament\Resources\KabKotaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKabKota extends EditRecord
{
    protected static string $resource = KabKotaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
