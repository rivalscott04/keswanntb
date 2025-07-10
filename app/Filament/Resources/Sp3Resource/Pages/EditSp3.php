<?php

namespace App\Filament\Resources\Sp3Resource\Pages;

use App\Filament\Resources\Sp3Resource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSp3 extends EditRecord
{
    protected static string $resource = Sp3Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
