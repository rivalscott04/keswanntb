<?php

namespace App\Filament\Resources\WewenangResource\Pages;

use App\Filament\Resources\WewenangResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWewenang extends EditRecord
{
    protected static string $resource = WewenangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
