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
        $data['tahap_verifikasi_id'] = TahapVerifikasi::where('urutan', 2)->first()->id;
        $data['status'] = 'menunggu';
        $data['jenis_pengajuan'] = 'pengeluaran';
        return $data;
    }
}