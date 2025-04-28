<?php

namespace App\Filament\Resources\JenisTernakResource\Pages;

use App\Filament\Resources\JenisTernakResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJenisTernak extends EditRecord
{
    protected static string $resource = JenisTernakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
