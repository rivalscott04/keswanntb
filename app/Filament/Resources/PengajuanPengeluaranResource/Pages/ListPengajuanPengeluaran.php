<?php

namespace App\Filament\Resources\PengajuanPengeluaranResource\Pages;

use Filament\Actions;
use Filament\Tables\Table;
use App\Services\PengajuanService;
use Filament\Tables\Filters\Filter;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\MultiSelectFilter;
use App\Filament\Resources\PengajuanPengeluaranResource;

class ListPengajuanPengeluaran extends ListRecords
{
    protected static string $resource = PengajuanPengeluaranResource::class;
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
        if ($user->is_admin || $user->wewenang->nama === 'Disnak Provinsi') {
            // Admin/Disnak NTB bisa lihat semua
            return $query;
        }
        if ($user->wewenang->nama === 'Disnak Kab/Kota') {
            // Disnak Kab/Kota hanya lihat pengajuan yang asal kab/kotanya
            return $query->where('kab_kota_asal_id', $user->kab_kota_id);
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
                        $ids = PengajuanService::queryPerluDitindaklanjutiFor($user, 'pengeluaran')->pluck('id');
                        $query->whereIn('id', $ids);
                    })
                    ->toggle(),
            ])->persistFiltersInSession();
    }
}