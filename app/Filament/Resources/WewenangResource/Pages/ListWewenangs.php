<?php

namespace App\Filament\Resources\WewenangResource\Pages;

use App\Filament\Resources\WewenangResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWewenangs extends ListRecords
{
    protected static string $resource = WewenangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
