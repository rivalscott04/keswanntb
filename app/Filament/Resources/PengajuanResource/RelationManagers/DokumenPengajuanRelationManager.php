<?php

namespace App\Filament\Resources\PengajuanResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;

class DokumenPengajuanRelationManager extends RelationManager
{
    protected static string $relationship = 'dokumenPengajuan';
    protected static ?string $title = 'Dokumen';
    protected static ?string $icon = 'heroicon-m-document-text';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->aktif())
            ->columns([
                Tables\Columns\TextColumn::make('jenis_dokumen')
                    ->label('Jenis Dokumen')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'rekomendasi_keswan' => 'info',
                        'skkh' => 'warning',
                        'surat_keterangan_veteriner' => 'success',
                        'izin_pengeluaran' => 'danger',
                        'izin_pemasukan' => 'danger',
                        'dokumen_lainnya' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'rekomendasi_keswan' => 'Rekomendasi Keswan',
                        'skkh' => 'SKKH',
                        'surat_keterangan_veteriner' => 'Surat Keterangan Veteriner',
                        'izin_pengeluaran' => 'Izin Pengeluaran',
                        'izin_pemasukan' => 'Izin Pemasukan',
                        'dokumen_lainnya' => 'Dokumen Lainnya',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('nomor_dokumen')
                    ->label('Nomor Dokumen')
                    ->searchable()
                    ->default('-'),
                
                Tables\Columns\TextColumn::make('nama_file')
                    ->label('Nama File')
                    ->searchable()
                    ->limit(50),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Diupload Oleh')
                    ->searchable()
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('ukuran_file_display')
                    ->label('Ukuran')
                    ->getStateUsing(fn ($record) => $record->ukuran_file_display),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Upload')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn ($record) => $record->url_download)
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum ada dokumen')
            ->emptyStateDescription('Dokumen akan muncul setelah di-generate atau diupload oleh dinas terkait.')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}

