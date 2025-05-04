<?php

namespace App\Filament\Resources\WewenangResource\Pages;

use App\Filament\Resources\WewenangResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageWewenang extends ManageRecords
{
    protected static string $resource = WewenangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalWidth('2xl'),
        ];
    }
}
