<?php

namespace App\Filament\Resources\KategoriTernakResource\Pages;

use App\Filament\Resources\KategoriTernakResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKategoriTernaks extends ListRecords
{
    protected static string $resource = KategoriTernakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
