<?php

namespace App\Filament\Resources\JenisTernakResource\Pages;

use App\Filament\Resources\JenisTernakResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJenisTernaks extends ListRecords
{
    protected static string $resource = JenisTernakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
