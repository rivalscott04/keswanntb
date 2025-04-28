<?php

namespace App\Filament\Resources\PengajuanPengeluaranResource\Pages;

use App\Filament\Resources\PengajuanPengeluaranResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengajuanPengeluarans extends ListRecords
{
    protected static string $resource = PengajuanPengeluaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
} 