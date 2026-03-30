<?php

namespace App\Filament\Resources\PengajuanResource\Pages;

use App\Filament\Resources\PengajuanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengajuan extends EditRecord
{
    protected static string $resource = PengajuanResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['pelabuhan_asal']) && $data['pelabuhan_asal'] === 'Lainnya' && isset($data['pelabuhan_asal_lainnya'])) {
            $data['pelabuhan_asal'] = $data['pelabuhan_asal_lainnya'];
        }
        unset($data['pelabuhan_asal_lainnya']);

        if (isset($data['pelabuhan_tujuan']) && $data['pelabuhan_tujuan'] === 'Lainnya' && isset($data['pelabuhan_tujuan_lainnya'])) {
            $data['pelabuhan_tujuan'] = $data['pelabuhan_tujuan_lainnya'];
        }
        unset($data['pelabuhan_tujuan_lainnya']);

        return $data;
    }

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
