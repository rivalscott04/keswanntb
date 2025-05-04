<?php

namespace App\Filament\Resources\TahapVerifikasiResource\Pages;

use App\Filament\Resources\TahapVerifikasiResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageTahapVerifikasi extends ManageRecords
{
    protected static string $resource = TahapVerifikasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
