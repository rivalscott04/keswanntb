<?php

namespace App\Filament\Resources\ProvinsiResource\Pages;

use App\Filament\Resources\ProvinsiResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageProvinsi extends ManageRecords
{
    protected static string $resource = ProvinsiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalWidth('2xl'),
        ];
    }

    public function mount(): void
    {
        abort_unless(auth()->user()->wewenang->nama === 'Administrator', 403);
    }
}
