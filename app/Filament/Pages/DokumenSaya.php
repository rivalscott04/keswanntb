<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use App\Models\DokumenPengajuan;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;

class DokumenSaya extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-down';
    protected static ?string $navigationLabel = 'Dokumen Saya';
    protected static ?string $navigationGroup = 'Pengajuan';
    protected static ?int $navigationSort = 10;
    protected static string $view = 'filament.pages.dokumen-saya';

    protected static ?string $title = 'Dokumen Saya';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                DokumenPengajuan::query()
                    ->whereHas('pengajuan', function (Builder $query) {
                        $query->where('user_id', auth()->id());
                    })
                    ->aktif()
            )
            ->columns([
                TextColumn::make('pengajuan.nomor_surat_permohonan')
                    ->label('Nomor Surat')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('pengajuan.jenis_pengajuan')
                    ->label('Jenis Pengajuan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'antar_kab_kota' => 'info',
                        'pengeluaran' => 'warning',
                        'pemasukan' => 'success',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'antar_kab_kota' => 'Antar Kab/Kota',
                        'pengeluaran' => 'Pengeluaran',
                        'pemasukan' => 'Pemasukan',
                    }),
                
                TextColumn::make('jenis_dokumen')
                    ->label('Jenis Dokumen')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'rekomendasi_keswan' => 'info',
                        'skkh' => 'warning',
                        'surat_keterangan_veteriner' => 'success',
                        'izin_pengeluaran' => 'danger',
                        'izin_pemasukan' => 'danger',
                        'dokumen_lainnya' => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'rekomendasi_keswan' => 'Rekomendasi Keswan',
                        'skkh' => 'SKKH',
                        'surat_keterangan_veteriner' => 'Surat Keterangan Veteriner',
                        'izin_pengeluaran' => 'Izin Pengeluaran',
                        'izin_pemasukan' => 'Izin Pemasukan',
                        'dokumen_lainnya' => 'Dokumen Lainnya',
                    }),
                
                TextColumn::make('nama_file')
                    ->label('Nama File')
                    ->searchable()
                    ->limit(30),
                
                TextColumn::make('ukuran_file_display')
                    ->label('Ukuran')
                    ->getStateUsing(fn ($record) => $record->ukuran_file_display),
                
                TextColumn::make('user.name')
                    ->label('Uploaded by')
                    ->searchable(),
                
                TextColumn::make('created_at')
                    ->label('Tanggal Upload')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('jenis_dokumen')
                    ->label('Jenis Dokumen')
                    ->options([
                        'rekomendasi_keswan' => 'Rekomendasi Keswan',
                        'skkh' => 'SKKH',
                        'surat_keterangan_veteriner' => 'Surat Keterangan Veteriner',
                        'izin_pengeluaran' => 'Izin Pengeluaran',
                        'izin_pemasukan' => 'Izin Pemasukan',
                        'dokumen_lainnya' => 'Dokumen Lainnya',
                    ]),
                
                SelectFilter::make('pengajuan.jenis_pengajuan')
                    ->label('Jenis Pengajuan')
                    ->options([
                        'antar_kab_kota' => 'Antar Kab/Kota',
                        'pengeluaran' => 'Pengeluaran',
                        'pemasukan' => 'Pemasukan',
                    ]),
                
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Dari Tanggal'),
                        DatePicker::make('created_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn ($record) => $record->url_download)
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50]);
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        return $user && $user->wewenang->nama === 'Pengguna' && $user->provinsi_verified_at;
    }
}