<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DokumenPengajuanResource\Pages;
use App\Filament\Resources\DokumenPengajuanResource\RelationManagers;
use App\Models\DokumenPengajuan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DokumenPengajuanResource extends Resource
{
    protected static ?string $model = DokumenPengajuan::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Dokumen';
    protected static ?string $navigationLabel = 'Dokumen Pengajuan';
    protected static ?string $slug = 'dokumen-pengajuan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('pengajuan_id')
                    ->label('Pengajuan')
                    ->relationship('pengajuan', 'nomor_surat_permohonan')
                    ->searchable()
                    ->preload()
                    ->required(),
                
                Forms\Components\Select::make('jenis_dokumen')
                    ->label('Jenis Dokumen')
                    ->options([
                        'rekomendasi_keswan' => 'Rekomendasi Keswan',
                        'skkh' => 'SKKH',
                        'surat_keterangan_veteriner' => 'Surat Keterangan Veteriner',
                        'izin_pengeluaran' => 'Izin Pengeluaran',
                        'izin_pemasukan' => 'Izin Pemasukan',
                        'dokumen_lainnya' => 'Dokumen Lainnya',
                    ])
                    ->required(),
                
                Forms\Components\FileUpload::make('path_file')
                    ->label('File Dokumen')
                    ->disk('public')
                    ->directory('dokumen-pengajuan')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                    ->maxSize(10240) // 10MB
                    ->visibility('private')
                    ->downloadable()
                    ->openable()
                    ->previewable(false)
                    ->required(),
                
                Forms\Components\TextInput::make('nama_file')
                    ->label('Nama File')
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\TextInput::make('nomor_dokumen')
                    ->label('Nomor Dokumen')
                    ->maxLength(255),
                
                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->columnSpanFull(),
                
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'aktif' => 'Aktif',
                        'tidak_aktif' => 'Tidak Aktif',
                    ])
                    ->default('aktif')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pengajuan.nomor_surat_permohonan')
                    ->label('Nomor Surat')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Uploader')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('jenis_dokumen')
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
                
                Tables\Columns\TextColumn::make('nama_file')
                    ->label('Nama File')
                    ->searchable()
                    ->limit(30),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'aktif' => 'success',
                        'tidak_aktif' => 'danger',
                    }),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Upload')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jenis_dokumen')
                    ->label('Jenis Dokumen')
                    ->options([
                        'rekomendasi_keswan' => 'Rekomendasi Keswan',
                        'skkh' => 'SKKH',
                        'surat_keterangan_veteriner' => 'Surat Keterangan Veteriner',
                        'izin_pengeluaran' => 'Izin Pengeluaran',
                        'izin_pemasukan' => 'Izin Pemasukan',
                        'dokumen_lainnya' => 'Dokumen Lainnya',
                    ]),
                
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'aktif' => 'Aktif',
                        'tidak_aktif' => 'Tidak Aktif',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn ($record) => $record->url_download)
                    ->openUrlInNewTab(),
                // Hanya uploader yang boleh mengedit dokumen
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => auth()->id() === $record->user_id),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Bulk delete hanya diizinkan untuk dokumen yang dipilih,
                    // dan Filament akan mengecek policy per-record.
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->check()),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDokumenPengajuans::route('/'),
            'create' => Pages\CreateDokumenPengajuan::route('/create'),
            'edit' => Pages\EditDokumenPengajuan::route('/{record}/edit'),
        ];
    }
}
