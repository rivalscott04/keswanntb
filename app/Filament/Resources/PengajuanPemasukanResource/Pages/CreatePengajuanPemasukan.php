<?php

namespace App\Filament\Resources\PengajuanPemasukanResource\Pages;

use App\Filament\Resources\PengajuanPemasukanResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\TahapVerifikasi;

class CreatePengajuanPemasukan extends CreateRecord
{
    protected static string $resource = PengajuanPemasukanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        // Pemasukan mulai dari urutan 2 (Tujuan) karena skip urutan 3 (Asal)
        $data['tahap_verifikasi_id'] = TahapVerifikasi::where('urutan', 2)->first()->id;
        $data['status'] = 'menunggu';
        $data['jenis_pengajuan'] = 'pemasukan';
        
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