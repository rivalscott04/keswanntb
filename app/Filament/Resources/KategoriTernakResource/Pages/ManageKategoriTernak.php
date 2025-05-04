<?php

namespace App\Filament\Resources\KategoriTernakResource\Pages;

use App\Filament\Resources\KategoriTernakResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageKategoriTernak extends ManageRecords
{
    protected static string $resource = KategoriTernakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalWidth('2xl'),
        ];
    }
}
