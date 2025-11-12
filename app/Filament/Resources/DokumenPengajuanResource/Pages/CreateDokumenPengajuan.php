<?php

namespace App\Filament\Resources\DokumenPengajuanResource\Pages;

use App\Filament\Resources\DokumenPengajuanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDokumenPengajuan extends CreateRecord
{
    protected static string $resource = DokumenPengajuanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }
}
