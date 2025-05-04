<?php

namespace App\Filament\Resources\PengajuanPemasukanResource\Pages;

use App\Filament\Resources\PengajuanPemasukanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengajuanPemasukan extends EditRecord
{
    protected static string $resource = PengajuanPemasukanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->visible(fn($record) => in_array($record->status, ['menunggu', 'ditolak'])),
        ];
    }

    public function getRelationManagers(): array
    {
        return [];
    }
}