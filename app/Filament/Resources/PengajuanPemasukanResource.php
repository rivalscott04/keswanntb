<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengajuanPemasukanResource\Pages;
use App\Models\Pengajuan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PengajuanPemasukanResource extends Resource
{
    protected static ?string $model = Pengajuan::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static ?string $navigationLabel = 'Pengajuan Pemasukan';
    protected static ?string $navigationGroup = 'Pengajuan';
    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('jenis_pengajuan', 'pemasukan');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('kategori_pengajuan')
                    ->label('Pilih Kategori Pengajuan')
                    ->options([
                        'baru' => 'Baru',
                        'perpanjangan' => 'Perpanjangan',
                    ])
                    ->required(),

                Forms\Components\Section::make('Dokumen')
                    ->schema([
                        Forms\Components\FileUpload::make('surat_permohonan')
                            ->label('Surat Permohonan Perusahaan')
                            ->required(),

                        Forms\Components\TextInput::make('nomor_surat_permohonan')
                            ->label('Nomor Surat Permohonan Perusahaan')
                            ->required(),

                        Forms\Components\DatePicker::make('tanggal_surat_permohonan')
                            ->label('Tanggal Surat Permohonan Perusahaan')
                            ->required(),

                        Forms\Components\FileUpload::make('skkh')
                            ->label('SKKH')
                            ->required(),

                        Forms\Components\TextInput::make('nomor_skkh')
                            ->label('No. SKKH')
                            ->required(),

                        Forms\Components\FileUpload::make('hasil_uji_lab')
                            ->label('Hasil Uji Lab')
                            ->required(),

                        Forms\Components\FileUpload::make('dokumen_lainnya')
                            ->label('Dokumen Lainnya (Jika Ada)')
                            ->multiple(),

                        Forms\Components\FileUpload::make('izin_ptsp_daerah')
                            ->label('Izin PTSP Daerah Pengeluaran')
                            ->required(),
                    ]),

                Forms\Components\Section::make('Lokasi')
                    ->schema([
                        Forms\Components\Select::make('kab_kota_tujuan_id')
                            ->label('Kab/Kota Tujuan Ternak')
                            ->relationship('kabKotaTujuan', 'nama')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('pelabuhan_tujuan')
                            ->label('Nama Pelabuhan Tujuan')
                            ->required(),

                        Forms\Components\Select::make('provinsi_asal_id')
                            ->label('Provinsi Asal Ternak')
                            ->relationship('provinsiAsal', 'nama')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('kab_kota_asal_id')
                            ->label('Kota Asal Ternak')
                            ->relationship('kabKotaAsal', 'nama')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('pelabuhan_asal')
                            ->label('Nama Pelabuhan Asal')
                            ->required(),
                    ]),

                Forms\Components\Section::make('Informasi Ternak')
                    ->schema([
                        Forms\Components\Select::make('jenis_ternak_id')
                            ->label('Jenis Ternak')
                            ->relationship('jenisTernak', 'nama')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('ras_ternak')
                            ->label('Ras Ternak')
                            ->required(),

                        Forms\Components\TextInput::make('jumlah_ternak')
                            ->label('Jumlah Ternak')
                            ->numeric()
                            ->required()
                            ->helperText('Kuota = 0'),
                    ]),

                Forms\Components\Select::make('tahun_pengajuan')
                    ->label('Tahun Pengajuan')
                    ->options(collect(range(date('Y'), date('Y') - 4))->mapWithKeys(fn($y) => [$y => $y])->toArray())
                    ->default(date('Y'))
                    ->required(),

                Forms\Components\Hidden::make('user_id')
                    ->default(auth()->id()),

                Forms\Components\Hidden::make('jenis_pengajuan')
                    ->default('pemasukan'),

                Forms\Components\Hidden::make('status')
                    ->default('pending'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_surat_permohonan')
                    ->label('Nomor Surat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_surat_permohonan')
                    ->label('Tanggal')
                    ->date(),
                Tables\Columns\TextColumn::make('provinsiAsal.nama')
                    ->label('Provinsi Asal'),
                Tables\Columns\TextColumn::make('kabKotaAsal.nama')
                    ->label('Kota Asal'),
                Tables\Columns\TextColumn::make('kabKotaTujuan.nama')
                    ->label('Tujuan'),
                Tables\Columns\TextColumn::make('jenisTernak.nama')
                    ->label('Jenis Ternak'),
                Tables\Columns\TextColumn::make('jumlah_ternak')
                    ->label('Jumlah'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'gray',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListPengajuanPemasukans::route('/'),
            'create' => Pages\CreatePengajuanPemasukan::route('/create'),
            'edit' => Pages\EditPengajuanPemasukan::route('/{record}/edit'),
        ];
    }
}