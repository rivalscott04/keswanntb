<?php

namespace App\Filament\Resources\PengajuanResource\Pages;

use Filament\Actions;
use Filament\Tables\Table;
use App\Services\PengajuanService;
use Filament\Tables\Filters\Filter;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PengajuanResource;
use Filament\Tables\Filters\MultiSelectFilter;

class ListPengajuan extends ListRecords
{
    protected static string $resource = PengajuanResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        if ($user->is_admin || $user->wewenang->nama === 'Pengguna') {
            return [
                Actions\CreateAction::make(),
            ];
        }
        return [];
    }

    public function getTableQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getTableQuery();

        if ($user->wewenang->nama === 'Pengguna') {
            // Pengusaha hanya melihat pengajuan miliknya
            return $query->where('user_id', $user->id);
        }
        if ($user->is_admin) {
            // Admin bisa lihat semua
            return $query;
        }
        if ($user->wewenang->nama === 'Disnak Provinsi') {
            // Disnak Provinsi: HARUS filter berdasarkan bidang
            // Setiap Disnak Provinsi harus punya bidang_id untuk mencegah tumpang tindih data antar bidang
            if ($user->bidang_id) {
                return $query->whereHas('jenisTernak', function ($q) use ($user) {
                    $q->where('bidang_id', $user->bidang_id);
                });
            }
            // Jika tidak ada bidang_id, return empty query (tidak bisa akses)
            return $query->whereRaw('1 = 0');
        }
        if ($user->wewenang->nama === 'Disnak Kab/Kota') {
            // Disnak Kab/Kota hanya lihat pengajuan yang asal/tujuan kab/kotanya
            return $query->where(function ($q) use ($user) {
                $q->where('kab_kota_asal_id', $user->kab_kota_id)
                    ->orWhere('kab_kota_tujuan_id', $user->kab_kota_id);
            });
        }
        if ($user->wewenang->nama === 'DPMPTSP') {
            // PTSP hanya lihat status disetujui/selesai
            return $query->whereIn('status', ['disetujui', 'selesai']);
        }
        return $query;
    }

    public function table(Table $table): Table
    {
        $currentYear = now()->year;
        $years = collect(range($currentYear, $currentYear - 4))->mapWithKeys(fn($y) => [$y => $y])->toArray();
        return parent::table($table)
            ->filters([
                MultiSelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'menunggu' => 'Menunggu',
                        'diproses' => 'Diproses',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                        'selesai' => 'Selesai',
                    ]),
                SelectFilter::make('tahun_pengajuan')
                    ->label('Tahun')
                    ->options($years)
                    ->default($currentYear),
                Filter::make('perlu_ditindaklanjuti')
                    ->label('Perlu Ditindaklanjuti')
                    ->query(function (Builder $query) {
                        $user = auth()->user();
                        $ids = PengajuanService::queryPerluDitindaklanjutiFor($user, 'antar_kab_kota')->pluck('id');
                        $query->whereIn('id', $ids);
                    })
                    ->toggle(),
            ])->persistFiltersInSession();
    }
}
