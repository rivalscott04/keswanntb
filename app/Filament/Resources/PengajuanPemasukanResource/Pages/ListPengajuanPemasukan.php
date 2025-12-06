<?php

namespace App\Filament\Resources\PengajuanPemasukanResource\Pages;

use App\Filament\Resources\PengajuanPemasukanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\MultiSelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use App\Services\PengajuanService;
use Filament\Tables\Table;

class ListPengajuanPemasukan extends ListRecords
{
    protected static string $resource = PengajuanPemasukanResource::class;
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
            // Disnak Provinsi: filter berdasarkan bidang jika ada
            if ($user->bidang_id) {
                return $query->whereHas('jenisTernak', function ($q) use ($user) {
                    $q->where('bidang_id', $user->bidang_id);
                });
            }
            // Jika tidak ada bidang_id, lihat semua
            return $query;
        }
        if ($user->wewenang->nama === 'Disnak Kab/Kota') {
            // Disnak Kab/Kota hanya lihat pengajuan yang tujuan kab/kotanya
            return $query->where('kab_kota_tujuan_id', $user->kab_kota_id);
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
                        $ids = PengajuanService::queryPerluDitindaklanjutiFor($user, 'pemasukan')->pluck('id');
                        $query->whereIn('id', $ids);
                    })
                    ->toggle(),
            ])->persistFiltersInSession();
    }
}