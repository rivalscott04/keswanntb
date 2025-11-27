<?php

namespace App\Filament\Resources\PengajuanPengeluaranResource\Pages;

use App\Models\TahapVerifikasi;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\PengajuanPengeluaranResource;

class CreatePengajuanPengeluaran extends CreateRecord
{
    protected static string $resource = PengajuanPengeluaranResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        // Pengeluaran mulai dari urutan 3 (Asal) karena skip urutan 2 (Tujuan)
        $data['tahap_verifikasi_id'] = TahapVerifikasi::where('urutan', 3)->first()->id;
        $data['status'] = 'menunggu';
        $data['jenis_pengajuan'] = 'pengeluaran';
        
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