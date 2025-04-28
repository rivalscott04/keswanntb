<?php

namespace App\Filament\Resources\PengajuanPengeluaranResource\Pages;

use App\Filament\Resources\PengajuanPengeluaranResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengajuanPengeluaran extends EditRecord
{
    protected static string $resource = PengajuanPengeluaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
} 