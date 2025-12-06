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

        // Jika user adalah Pengguna (pengusaha), hanya tampilkan dokumen dari pengajuan miliknya
        if ($user->wewenang->nama === 'Pengguna') {
            return $query->whereHas('pengajuan', function (Builder $q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        // Admin dan Disnak Provinsi bisa lihat semua
        if ($user->is_admin || $user->wewenang->nama === 'Disnak Provinsi') {
            return $query;
        }

        // Disnak Kab/Kota hanya lihat dokumen dari pengajuan yang asal/tujuan kab/kotanya
        if ($user->wewenang->nama === 'Disnak Kab/Kota') {
            return $query->whereHas('pengajuan', function (Builder $q) use ($user) {
                $q->where('kab_kota_asal_id', $user->kab_kota_id)
                    ->orWhere('kab_kota_tujuan_id', $user->kab_kota_id);
            });
        }

        // DPMPTSP hanya lihat dokumen dari pengajuan yang sudah disetujui/selesai
        if ($user->wewenang->nama === 'DPMPTSP') {
            return $query->whereHas('pengajuan', function (Builder $q) {
                $q->whereIn('status', ['disetujui', 'selesai']);
            });
        }

        return $query;
    }
}
