<?php

namespace App\Filament\Resources\PengajuanResource\Pages;

use App\Models\TahapVerifikasi;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\PengajuanResource;

class CreatePengajuan extends CreateRecord
{
    protected static string $resource = PengajuanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['tahap_verifikasi_id'] = TahapVerifikasi::where('urutan', 2)->first()->id;
        $data['status'] = 'menunggu';
        $data['jenis_pengajuan'] = 'antar_kab_kota';
        return $data;
    }
}
