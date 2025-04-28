<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengajuanResource\Pages;
use App\Filament\Resources\PengajuanResource\RelationManagers;
use App\Models\Pengajuan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class PengajuanResource extends Resource
{
    protected static ?string $model = Pengajuan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Pengajuan';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Pengajuan';
    protected static ?string $pluralModelLabel = 'Pengajuan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('tahun_pengajuan')
                    ->label('Tahun Pengajuan')
                    ->options(collect(range(date('Y'), date('Y') - 4))->mapWithKeys(fn($y) => [$y => $y])->toArray())
                    ->default(date('Y'))
                    ->required()
                    ->live(),

                Forms\Components\Select::make('kategori_ternak_id')
                    ->label('Kategori Ternak')
                    ->relationship('kategoriTernak', 'nama')
                    ->required()
                    ->live(),

                Forms\Components\Select::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->options([
                        'jantan' => 'Jantan',
                        'betina' => 'Betina',
                    ])
                    ->required()
                    ->live(),

                Forms\Components\Section::make('Dokumen')
                    ->schema([
                        Forms\Components\FileUpload::make('surat_permohonan')
                            ->label('Surat Permohonan Perusahaan')
                            ->required(false),

                        Forms\Components\TextInput::make('nomor_surat_permohonan')
                            ->label('Nomor Surat Permohonan Perusahaan')
                            ->required(),

                        Forms\Components\DatePicker::make('tanggal_surat_permohonan')
                            ->label('Tanggal Surat Permohonan Perusahaan')
                            ->required(),

                        Forms\Components\FileUpload::make('skkh')
                            ->label('SKKH')
                            ->required(false),

                        Forms\Components\TextInput::make('nomor_skkh')
                            ->label('No. SKKH')
                            ->required(),

                        Forms\Components\FileUpload::make('hasil_uji_lab')
                            ->label('Hasil Uji Lab')
                            ->required(false),

                        Forms\Components\FileUpload::make('dokumen_lainnya')
                            ->label('Dokumen Lainnya (Jika Ada)')
                            ->multiple(),
                    ]),

                Forms\Components\Section::make('Lokasi')
                    ->schema([
                        Forms\Components\Select::make('kab_kota_asal_id')
                            ->label('Kab/Kota Asal Ternak')
                            ->relationship('kabKotaAsal', 'nama')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('pelabuhan_asal')
                            ->label('Nama Pelabuhan Asal')
                            ->required(),

                        Forms\Components\Select::make('kab_kota_tujuan_id')
                            ->label('Kab/Kota Tujuan Ternak')
                            ->relationship('kabKotaTujuan', 'nama')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live(),

                        Forms\Components\TextInput::make('pelabuhan_tujuan')
                            ->label('Nama Pelabuhan Tujuan')
                            ->required(),
                    ]),

                Forms\Components\Section::make('Informasi Ternak')
                    ->schema([
                        Forms\Components\Select::make('jenis_ternak_id')
                            ->label('Jenis Ternak')
                            ->options(fn(callable $get) => \App\Models\JenisTernak::where('kategori_ternak_id', $get('kategori_ternak_id'))->pluck('nama', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live(),

                        Forms\Components\TextInput::make('ras_ternak')
                            ->label('Ras Ternak')
                            ->required(),

                        Forms\Components\TextInput::make('jumlah_ternak')
                            ->label('Jumlah Ternak')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(
                                fn(callable $get) =>
                                \App\Models\Kuota::where('tahun', $get('tahun_pengajuan'))
                                    ->where('jenis_ternak_id', $get('jenis_ternak_id'))
                                    ->where('kab_kota_id', $get('kab_kota_tujuan_id'))
                                    ->where('jenis_kelamin', $get('jenis_kelamin'))
                                    ->value('kuota') ?? 0
                            )
                            ->helperText(
                                fn(callable $get) =>
                                'Kuota: ' . (\App\Models\Kuota::where('tahun', $get('tahun_pengajuan'))
                                    ->where('jenis_ternak_id', $get('jenis_ternak_id'))
                                    ->where('kab_kota_id', $get('kab_kota_tujuan_id'))
                                    ->where('jenis_kelamin', $get('jenis_kelamin'))
                                    ->value('kuota') ?? 0)
                            )
                            ->required()
                            ->reactive(),
                    ]),

                Forms\Components\Hidden::make('jenis_pengajuan')
                    ->default('antar_kab_kota'),
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
                Tables\Columns\TextColumn::make('kabKotaAsal.nama')
                    ->label('Asal'),
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
            'index' => Pages\ListPengajuans::route('/'),
            'create' => Pages\CreatePengajuan::route('/create'),
            'edit' => Pages\EditPengajuan::route('/{record}/edit'),
        ];
    }
}
