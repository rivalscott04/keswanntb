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
        $data['tahap_verifikasi_id'] = TahapVerifikasi::where('urutan', 3)->first()->id;
        $data['status'] = 'menunggu';
        $data['jenis_pengajuan'] = 'pemasukan';
        return $data;
    }
}