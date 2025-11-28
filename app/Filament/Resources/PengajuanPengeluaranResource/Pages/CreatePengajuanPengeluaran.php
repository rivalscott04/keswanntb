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

        // Handle untuk Bibit Sapi: set jenis_kelamin berdasarkan jumlah_jantan dan jumlah_betina
        $jenisTernak = \App\Models\JenisTernak::find($data['jenis_ternak_id'] ?? null);
        if ($jenisTernak && $jenisTernak->nama === 'Bibit Sapi') {
            // Untuk Bibit Sapi, jenis_kelamin bisa null atau kita set default
            // Jumlah total dihitung dari jumlah_jantan + jumlah_betina
            $jumlahJantan = (int)($data['jumlah_jantan'] ?? 0);
            $jumlahBetina = (int)($data['jumlah_betina'] ?? 0);
            
            // Validasi: minimal salah satu harus diisi
            if ($jumlahJantan == 0 && $jumlahBetina == 0) {
                throw new \Illuminate\Validation\ValidationException(
                    validator([], []),
                    ['jumlah_jantan' => ['Minimal salah satu (jantan atau betina) harus diisi.']]
                );
            }
            
            $data['jumlah_ternak'] = $jumlahJantan + $jumlahBetina;
            
            // Set jenis_kelamin untuk backward compatibility (jika hanya salah satu yang diisi)
            if ($jumlahJantan > 0 && $jumlahBetina == 0) {
                $data['jenis_kelamin'] = 'jantan';
            } elseif ($jumlahBetina > 0 && $jumlahJantan == 0) {
                $data['jenis_kelamin'] = 'betina';
            } else {
                // Jika keduanya ada, set jenis_kelamin ke null atau 'campuran'
                // Tapi karena field tidak nullable, kita set default
                $data['jenis_kelamin'] = 'jantan'; // Default, tapi akan dihandle terpisah di service
            }
        } else {
            // Untuk jenis ternak lain, pastikan jumlah_jantan dan jumlah_betina null
            $data['jumlah_jantan'] = null;
            $data['jumlah_betina'] = null;
        }
        
        return $data;
    }
}