<?php

namespace App\Filament\Resources\PengajuanPemasukanResource\Pages;

use App\Filament\Resources\PengajuanPemasukanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengajuanPemasukans extends ListRecords
{
    protected static string $resource = PengajuanPemasukanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
} 