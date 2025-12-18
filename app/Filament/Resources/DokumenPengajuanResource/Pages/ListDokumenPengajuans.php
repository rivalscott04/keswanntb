<?php

namespace App\Filament\Resources\DokumenPengajuanResource\Pages;

use App\Filament\Resources\DokumenPengajuanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListDokumenPengajuans extends ListRecords
{
    protected static string $resource = DokumenPengajuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getTableQuery();

        // Secara umum, menu "Dokumen Pengajuan" sekarang hanya menampilkan
        // dokumen yang DIUPLOAD oleh user yang sedang login (user_id = auth()->id())
        // sehingga dokumen dari akun lain akan dipindahkan ke menu "Dokumen Saya".
        $query = $query->where('user_id', $user->id);

        // Batasi juga berdasarkan keterkaitan pengajuan, mengikuti aturan lama:

        // Jika user adalah Pengguna (pengusaha), hanya dari pengajuan miliknya
        if ($user->wewenang->nama === 'Pengguna') {
            return $query->whereHas('pengajuan', function (Builder $q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        // Disnak Kab/Kota hanya dari pengajuan yang asal/tujuan kab/kotanya
        if ($user->wewenang->nama === 'Disnak Kab/Kota') {
            return $query->whereHas('pengajuan', function (Builder $q) use ($user) {
                $q->where('kab_kota_asal_id', $user->kab_kota_id)
                    ->orWhere('kab_kota_tujuan_id', $user->kab_kota_id);
            });
        }

        // DPMPTSP hanya dari pengajuan yang sudah disetujui/selesai
        if ($user->wewenang->nama === 'DPMPTSP') {
            return $query->whereHas('pengajuan', function (Builder $q) {
                $q->whereIn('status', ['disetujui', 'selesai']);
            });
        }

        // Admin & Disnak Provinsi: semua dokumen yang ia upload sendiri
        return $query;
    }
}
