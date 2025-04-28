<?php

namespace App\Filament\Resources\KategoriTernakResource\Pages;

use App\Filament\Resources\KategoriTernakResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKategoriTernak extends EditRecord
{
    protected static string $resource = KategoriTernakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
