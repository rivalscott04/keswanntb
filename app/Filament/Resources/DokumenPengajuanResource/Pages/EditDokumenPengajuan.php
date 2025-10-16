<?php

namespace App\Filament\Resources\DokumenPengajuanResource\Pages;

use App\Filament\Resources\DokumenPengajuanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDokumenPengajuan extends EditRecord
{
    protected static string $resource = DokumenPengajuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
