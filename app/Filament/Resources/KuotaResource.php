<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KuotaResource\Pages;
use App\Filament\Resources\KuotaResource\RelationManagers;
use App\Filament\Traits\HasAdminOnlyAccess;
use App\Models\Kuota;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class KuotaResource extends Resource
{
    use HasAdminOnlyAccess;

    protected static ?string $model = Kuota::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Kuota';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'kuota';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('jenis_ternak_id')
                    ->label('Jenis Ternak')
                    ->options(function () {
                        return \App\Models\JenisTernak::with('kategoriTernak')
                            ->get()
                            ->groupBy('kategoriTernak.nama')
                            ->map(function ($jenisTernakGroup, $kategoriNama) {
                                return $jenisTernakGroup->pluck('nama', 'id');
                            })
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live(),
                Forms\Components\Select::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->options([
                        'jantan' => 'Jantan',
                        'betina' => 'Betina',
                        'gabung' => 'Gabung',
                    ])
                    ->required(),
                Forms\Components\Select::make('pulau')
                    ->label('Pulau')
                    ->options([
                        'Lombok' => 'Lombok',
                        'Sumbawa' => 'Sumbawa',
                    ])
                    ->required()
                    ->live()
                    ->helperText('Pilih "Lombok" untuk kuota terintegrasi semua kab/kota di Lombok'),
                Forms\Components\Select::make('jenis_kuota')
                    ->label('Jenis')
                    ->options([
                        'pemasukan' => 'Pemasukan',
                        'pengeluaran' => 'Pengeluaran',
                    ])
                    ->required()
                    ->live(),
                Forms\Components\Select::make('kab_kota_id')
                    ->label('Kab/Kota')
                    ->options(function (callable $get) {
                        $pulau = $get('pulau');
                        $jenisKuota = $get('jenis_kuota');
                        $jenisTernakId = $get('jenis_ternak_id');
                        
                        // Cek apakah jenis ternak adalah Bibit Sapi
                        $isBibitSapi = false;
                        if ($jenisTernakId) {
                            $jenisTernak = \App\Models\JenisTernak::find($jenisTernakId);
                            $isBibitSapi = $jenisTernak && $jenisTernak->nama === 'Bibit Sapi';
                        }
                        
                        // Daftar kab/kota di Pulau Lombok
                        $kabKotaLombok = [
                            'Kota Mataram',
                            'Kab. Lombok Barat',
                            'Kab. Lombok Tengah',
                            'Kab. Lombok Timur',
                            'Kab. Lombok Utara'
                        ];
                        
                        // Daftar kab/kota di Pulau Sumbawa
                        $kabKotaSumbawa = [
                            'Kab. Sumbawa Barat',
                            'Kab. Sumbawa',
                            'Kab. Dompu',
                            'Kab. Bima',
                            'Kota Bima'
                        ];
                        
                        // Jika jenis ternak adalah Bibit Sapi, filter berdasarkan pulau yang dipilih
                        if ($isBibitSapi) {
                            if ($pulau === 'Lombok') {
                                // Jika pulau Lombok, tampilkan hanya kab/kota Lombok
                                return \App\Models\KabKota::whereIn('nama', $kabKotaLombok)
                                    ->pluck('nama', 'id');
                            } elseif ($pulau === 'Sumbawa') {
                                // Jika pulau Sumbawa, tampilkan hanya kab/kota Sumbawa
                                return \App\Models\KabKota::whereIn('nama', $kabKotaSumbawa)
                                    ->pluck('nama', 'id');
                            }
                            // Jika belum pilih pulau, tampilkan semua
                            return \App\Models\KabKota::pluck('nama', 'id');
                        }
                        
                        // Jika pulau Lombok dan jenis kuota pemasukan, tampilkan hanya kab/kota Lombok
                        if ($pulau === 'Lombok' && $jenisKuota === 'pemasukan') {
                            return \App\Models\KabKota::whereIn('nama', $kabKotaLombok)
                                ->pluck('nama', 'id');
                        }
                        
                        // Jika pulau Lombok dan jenis kuota pengeluaran, tidak perlu pilih kab/kota (global)
                        if ($pulau === 'Lombok' && $jenisKuota === 'pengeluaran') {
                            return [];
                        }
                        
                        // Untuk pulau lain, tampilkan semua kab/kota
                        return \App\Models\KabKota::pluck('nama', 'id');
                    })
                    ->visible(function (callable $get) {
                        $pulau = $get('pulau');
                        $jenisKuota = $get('jenis_kuota');
                        $jenisTernakId = $get('jenis_ternak_id');
                        
                        // Cek apakah jenis ternak adalah Bibit Sapi
                        $isBibitSapi = false;
                        if ($jenisTernakId) {
                            $jenisTernak = \App\Models\JenisTernak::find($jenisTernakId);
                            $isBibitSapi = $jenisTernak && $jenisTernak->nama === 'Bibit Sapi';
                        }
                        
                        // Jika Bibit Sapi, selalu tampilkan field kab/kota
                        if ($isBibitSapi) {
                            return true;
                        }
                        
                        // Tampilkan jika:
                        // 1. Bukan pulau Lombok, ATAU
                        // 2. Pulau Lombok dan jenis kuota pemasukan (per kab/kota)
                        return $pulau !== 'Lombok' || ($pulau === 'Lombok' && $jenisKuota === 'pemasukan');
                    })
                    ->required(function (callable $get) {
                        $pulau = $get('pulau');
                        $jenisKuota = $get('jenis_kuota');
                        $jenisTernakId = $get('jenis_ternak_id');
                        
                        // Cek apakah jenis ternak adalah Bibit Sapi
                        $isBibitSapi = false;
                        if ($jenisTernakId) {
                            $jenisTernak = \App\Models\JenisTernak::find($jenisTernakId);
                            $isBibitSapi = $jenisTernak && $jenisTernak->nama === 'Bibit Sapi';
                        }
                        
                        // Jika Bibit Sapi, selalu required
                        if ($isBibitSapi) {
                            return true;
                        }
                        
                        // Required jika:
                        // 1. Bukan pulau Lombok, ATAU
                        // 2. Pulau Lombok dan jenis kuota pemasukan
                        return $pulau !== 'Lombok' || ($pulau === 'Lombok' && $jenisKuota === 'pemasukan');
                    })
                    ->live()
                    ->helperText(function (callable $get) {
                        $pulau = $get('pulau');
                        $jenisKuota = $get('jenis_kuota');
                        $jenisTernakId = $get('jenis_ternak_id');
                        
                        // Cek apakah jenis ternak adalah Bibit Sapi
                        $isBibitSapi = false;
                        if ($jenisTernakId) {
                            $jenisTernak = \App\Models\JenisTernak::find($jenisTernakId);
                            $isBibitSapi = $jenisTernak && $jenisTernak->nama === 'Bibit Sapi';
                        }
                        
                        if ($isBibitSapi) {
                            if ($pulau === 'Lombok') {
                                return 'Pilih kab/kota di Pulau Lombok untuk kuota Bibit Sapi';
                            } elseif ($pulau === 'Sumbawa') {
                                return 'Pilih kab/kota di Pulau Sumbawa untuk kuota Bibit Sapi';
                            }
                            return 'Pilih pulau terlebih dahulu untuk melihat kab/kota yang tersedia';
                        }
                        
                        if ($pulau === 'Lombok' && $jenisKuota === 'pemasukan') {
                            return 'Pilih kab/kota di Pulau Lombok untuk kuota pemasukan spesifik per kab/kota';
                        }
                        if ($pulau === 'Lombok' && $jenisKuota === 'pengeluaran') {
                            return 'Kuota pengeluaran dari Pulau Lombok adalah global (tidak perlu pilih kab/kota)';
                        }
                        return null;
                    }),
                Forms\Components\TextInput::make('tahun')
                    ->label('Tahun')
                    ->required()
                    ->numeric()
                    ->minValue(date('Y'))
                    ->default(date('Y')),
                Forms\Components\TextInput::make('kuota')
                    ->label('Jumlah Kuota')
                    ->required()
                    ->numeric()
                    ->minValue(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('jenisTernak.nama')
                    ->label('Jenis Ternak')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->sortable(),
                Tables\Columns\TextColumn::make('lokasi_display')
                    ->label('Lokasi')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn($record) => $record->lokasi_display),
                Tables\Columns\TextColumn::make('jenis_kuota')
                    ->label('Jenis')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tahun')
                    ->label('Tahun')
                    ->sortable(),
                Tables\Columns\TextColumn::make('kuota')
                    ->label('Total Kuota')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kuota_terpakai')
                    ->label('Kuota Terpakai')
                    ->numeric()
                    ->sortable()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('kuota_sisa')
                    ->label('Kuota Sisa')
                    ->numeric()
                    ->sortable()
                    ->color(fn($record) => $record->kuota_sisa > 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListKuotas::route('/'),
            'create' => Pages\CreateKuota::route('/create'),
            'edit' => Pages\EditKuota::route('/{record}/edit'),
        ];
    }
}
