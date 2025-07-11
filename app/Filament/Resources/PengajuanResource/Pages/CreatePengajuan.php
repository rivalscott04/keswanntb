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
        
        // Handle pelabuhan asal
        if (isset($data['pelabuhan_asal']) && $data['pelabuhan_asal'] === 'Lainnya' && isset($data['pelabuhan_asal_lainnya'])) {
            $data['pelabuhan_asal'] = $data['pelabuhan_asal_lainnya'];
        }
        unset($data['pelabuhan_asal_lainnya']);
        
        // Handle pelabuhan tujuan
        if (isset($data['pelabuhan_tujuan']) && $data['pelabuhan_tujuan'] === 'Lainnya' && isset($data['pelabuhan_tujuan_lainnya'])) {
            $data['pelabuhan_tujuan'] = $data['pelabuhan_tujuan_lainnya'];
        }
        unset($data['pelabuhan_tujuan_lainnya']);
        
        return $data;
    }
}
