<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Kuota;
use App\Models\KabKota;
use Filament\Forms\Form;
use App\Models\Pengajuan;
use Filament\Tables\Table;
use App\Models\JenisTernak;
use Filament\Resources\Resource;
use App\Services\PengajuanService;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PengajuanResource\Pages;
use App\Filament\Components\UnverifiedAccountNotification;
use App\Services\PelabuhanService;

class PengajuanResource extends Resource
{
    protected static ?string $model = Pengajuan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Pengajuan';
    protected static ?int $navigationSort = 1;
    protected static ?string $label = 'Pengajuan Antar Kab/Kota NTB';
    protected static ?string $slug = 'pengajuan';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('jenis_pengajuan', 'antar_kab_kota');
    }

    public static function form(Form $form): Form
    {
        $cekKuotaTersedia = function (callable $get) {
            $tahun = $get('tahun_pengajuan');
            $jenisTernakId = $get('jenis_ternak_id');
            $kabKotaAsalId = $get('kab_kota_asal_id');
            $kabKotaTujuanId = $get('kab_kota_tujuan_id');
            $jenisKelamin = $get('jenis_kelamin');

            // Cek apakah jenis ternak ini memerlukan kuota untuk pengeluaran
            $perluKuotaPengeluaran = \App\Models\PenggunaanKuota::isKuotaRequired($jenisTernakId, 'pengeluaran');
            
            // Cek apakah jenis ternak ini memerlukan kuota untuk pemasukan
            $perluKuotaPemasukan = \App\Models\PenggunaanKuota::isKuotaRequired($jenisTernakId, 'pemasukan', $kabKotaTujuanId);

            // Daftar kab/kota di pulau Lombok
            $kabKotaLombok = [
                'Kota Mataram',
                'Kab. Lombok Barat', 
                'Kab. Lombok Tengah',
                'Kab. Lombok Timur',
                'Kab. Lombok Utara'
            ];

            // Cek apakah kab/kota asal ada di Lombok
            $kabKotaAsal = \App\Models\KabKota::find($kabKotaAsalId);
            $kabKotaTujuan = \App\Models\KabKota::find($kabKotaTujuanId);
            
            $isLombokAsal = $kabKotaAsal && in_array($kabKotaAsal->nama, $kabKotaLombok);
            $isLombokTujuan = $kabKotaTujuan && in_array($kabKotaTujuan->nama, $kabKotaLombok);

            // Untuk pengajuan antar kab/kota, berdasarkan hasil rapat supply-demand:
            // - Kuota pengeluaran dari kab/kota di pulau Sumbawa TIDAK dikurangi
            // - Yang dikurangi hanya kuota pemasukan ke kab/kota di pulau Lombok
            // - Kuota pengeluaran dari kab/kota di pulau Lombok tetap dikurangi (global)
            if ($perluKuotaPengeluaran) {
                if ($isLombokAsal) {
                    // Kuota pengeluaran dari pulau Lombok (akan dikurangi)
                    $kuotaPengeluaran = \App\Models\PenggunaanKuota::getKuotaTersisaLombok(
                        $tahun, $jenisTernakId, $jenisKelamin, 'pengeluaran'
                    );
                    $lokasiKuota = 'Pulau Lombok';
                } else {
                    // Kuota pengeluaran dari kab/kota di Sumbawa (TIDAK akan dikurangi)
                    // Tetap tampilkan untuk informasi, tapi tidak akan divalidasi
                    $kuotaPengeluaran = \App\Models\PenggunaanKuota::getKuotaTersisa(
                        $tahun, $jenisTernakId, $kabKotaAsalId, $jenisKelamin, 'pengeluaran'
                    );
                    $lokasiKuota = ($kabKotaAsal ? $kabKotaAsal->nama : 'Tidak Diketahui') . ' (tidak dikurangi)';
                }
            } else {
                $kuotaPengeluaran = 'Tidak ada kuota (bebas keluar)';
                $lokasiKuota = $kabKotaAsal ? $kabKotaAsal->nama : 'Tidak Diketahui';
            }

            // Cek kuota pemasukan ke tujuan
            $kuotaPemasukan = 0;
            $lokasiPemasukan = 'Tidak Diketahui';
            if ($kabKotaTujuanId) {
                if ($perluKuotaPemasukan) {
                    // Untuk pemasukan, selalu gunakan kuota per kab/kota
                    // Kuota pemasukan untuk Lombok adalah per kab/kota (spesifik), bukan global
                    // Kuota pemasukan untuk Sumbawa juga per kab/kota
                    $kuotaPemasukan = \App\Models\PenggunaanKuota::getKuotaTersisa(
                        $tahun, 
                        $jenisTernakId, 
                        $kabKotaTujuanId, 
                        $jenisKelamin, 
                        'pemasukan', 
                        $isLombokTujuan ? 'Lombok' : null
                    );
                } else {
                    $kuotaPemasukan = 'Tidak ada kuota (bebas masuk)';
                }
                $lokasiPemasukan = $kabKotaTujuan ? $kabKotaTujuan->nama : 'Tidak Diketahui';
            }

            return [
                'kuota' => $kuotaPengeluaran,
                'lokasi' => $lokasiKuota,
                'pengeluaran' => $kuotaPengeluaran,
                'pemasukan' => $kuotaPemasukan,
                'lokasi_pemasukan' => $lokasiPemasukan
            ];
        };

        return $form
            ->schema([
                Forms\Components\Select::make('tahun_pengajuan')
                    ->label('Tahun Pengajuan')
                    ->options(collect(range(date('Y'), date('Y') - 4))->mapWithKeys(fn($y) => [$y => $y])->toArray())
                    ->default(date('Y'))
                    ->required()
                    ->live()
                    ->columnSpanFull(),

                Forms\Components\Section::make('Lokasi')
                    ->schema([
                        Forms\Components\Select::make('kab_kota_asal_id')
                            ->label('Kab/Kota Asal Ternak')
                            ->options(
                                fn(callable $get) =>
                                KabKota::when($get('kab_kota_tujuan_id'), function ($query, $tujuanId) {
                                    return $query->where('id', '!=', $tujuanId);
                                })->pluck('nama', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live(),

                        Forms\Components\Select::make('pelabuhan_asal')
                            ->label('Nama Pelabuhan Asal')
                            ->options(
                                fn(callable $get) => PelabuhanService::getPelabuhanOptionsWithLoading()
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->placeholder(fn() => PelabuhanService::getPelabuhanPlaceholder())
                            ->helperText(fn() => PelabuhanService::getPelabuhanHelperText())
                            ->loadingMessage('Memuat data pelabuhan dari API Kemenhub...')
                            ->disabled(fn() => PelabuhanService::isDataLoading()),

                        Forms\Components\TextInput::make('pelabuhan_asal_lainnya')
                            ->label('Nama Pelabuhan Asal (Lainnya)')
                            ->visible(fn(callable $get) => $get('pelabuhan_asal') === 'Lainnya')
                            ->required(fn(callable $get) => $get('pelabuhan_asal') === 'Lainnya')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('kab_kota_tujuan_id')
                            ->label('Kab/Kota Tujuan Ternak')
                            ->options(
                                fn(callable $get) =>
                                KabKota::when($get('kab_kota_asal_id'), function ($query, $asalId) {
                                    return $query->where('id', '!=', $asalId);
                                })->pluck('nama', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                if ($state === $get('kab_kota_asal_id')) {
                                    $set('kab_kota_tujuan_id', null);
                                }
                            })
                            ->rules(['different:kab_kota_asal_id'])
                            ->validationMessages([
                                'different' => 'Kab/Kota tujuan tidak boleh sama dengan asal.',
                            ]),

                        Forms\Components\Select::make('pelabuhan_tujuan')
                            ->label('Nama Pelabuhan Tujuan')
                            ->options(
                                fn(callable $get) => PelabuhanService::getPelabuhanOptionsWithLoading()
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->placeholder(fn() => PelabuhanService::getPelabuhanPlaceholder())
                            ->helperText(fn() => PelabuhanService::getPelabuhanHelperText())
                            ->loadingMessage('Memuat data pelabuhan dari API Kemenhub...')
                            ->disabled(fn() => PelabuhanService::isDataLoading()),

                        Forms\Components\TextInput::make('pelabuhan_tujuan_lainnya')
                            ->label('Nama Pelabuhan Tujuan (Lainnya)')
                            ->visible(fn(callable $get) => $get('pelabuhan_tujuan') === 'Lainnya')
                            ->required(fn(callable $get) => $get('pelabuhan_tujuan') === 'Lainnya')
                            ->columnSpanFull(),
                    ])->columns(),

                Forms\Components\Section::make('Informasi Ternak')
                    ->schema([
                        Forms\Components\Select::make('kategori_ternak_id')
                            ->label('Kategori Ternak')
                            ->relationship('kategoriTernak', 'nama')
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn(callable $set) => $set('jenis_ternak_id', null)),

                        Forms\Components\Select::make('jenis_ternak_id')
                            ->label('Jenis Ternak')
                            ->options(fn(callable $get) => JenisTernak::where('kategori_ternak_id', $get('kategori_ternak_id'))->pluck('nama', 'id'))
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
                            ->required()
                            ->live(),

                        Forms\Components\TextInput::make('ras_ternak')
                            ->label('Ras Ternak')
                            ->required(),

                        Forms\Components\TextInput::make('jumlah_ternak')
                            ->label('Jumlah Ternak')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(function (callable $get) use ($cekKuotaTersedia) {
                                $kuotaData = $cekKuotaTersedia($get);
                                $kuotaPengeluaran = $kuotaData['pengeluaran'];
                                
                                // Cek apakah asal di Lombok atau Sumbawa
                                $kabKotaAsalId = $get('kab_kota_asal_id');
                                $kabKotaAsal = $kabKotaAsalId ? \App\Models\KabKota::find($kabKotaAsalId) : null;
                                $kabKotaLombok = [
                                    'Kota Mataram',
                                    'Kab. Lombok Barat', 
                                    'Kab. Lombok Tengah',
                                    'Kab. Lombok Timur',
                                    'Kab. Lombok Utara'
                                ];
                                $isLombokAsal = $kabKotaAsal && in_array($kabKotaAsal->nama, $kabKotaLombok);
                                
                                // Jika asal di Sumbawa, tidak ada batasan maksimal untuk kuota pengeluaran
                                // karena kuota pengeluaran Sumbawa tidak akan dikurangi
                                if (!$isLombokAsal) {
                                    return null;
                                }
                                
                                // Jika tidak ada kuota atau string, tidak ada batasan maksimal
                                if (!is_numeric($kuotaPengeluaran)) {
                                    return null;
                                }
                                return $kuotaPengeluaran;
                            })
                            ->helperText(function (callable $get) use ($cekKuotaTersedia) {
                                $kuotaData = $cekKuotaTersedia($get);
                                $text = 'Kuota tersedia: ' . $kuotaData['pengeluaran'] . ' (Pengeluaran - ' . $kuotaData['lokasi'] . ')';

                                // Selalu tampilkan info kuota pemasukan ketika tujuan sudah dipilih,
                                // termasuk saat kuota bernilai 0 agar pengguna tahu tidak ada kuota tersisa.
                                if ($get('kab_kota_tujuan_id')) {
                                    $text .= ' | ' . $kuotaData['pemasukan'] . ' (Pemasukan - ' . $kuotaData['lokasi_pemasukan'] . ')';
                                }

                                return $text;
                            })
                            ->rules([
                                fn (callable $get) => function (string $attribute, $value, \Closure $fail) use ($get, $cekKuotaTersedia) {
                                    $kuotaData = $cekKuotaTersedia($get);
                                    $kuotaPengeluaran = $kuotaData['pengeluaran'];
                                    
                                    // Cek apakah asal di Lombok atau Sumbawa
                                    $kabKotaAsalId = $get('kab_kota_asal_id');
                                    $kabKotaAsal = $kabKotaAsalId ? \App\Models\KabKota::find($kabKotaAsalId) : null;
                                    $kabKotaLombok = [
                                        'Kota Mataram',
                                        'Kab. Lombok Barat', 
                                        'Kab. Lombok Tengah',
                                        'Kab. Lombok Timur',
                                        'Kab. Lombok Utara'
                                    ];
                                    $isLombokAsal = $kabKotaAsal && in_array($kabKotaAsal->nama, $kabKotaLombok);
                                    
                                    // Jika asal di Sumbawa, tidak perlu validasi kuota pengeluaran
                                    // karena kuota pengeluaran Sumbawa tidak akan dikurangi
                                    if (!$isLombokAsal) {
                                        return;
                                    }
                                    
                                    // Validasi hanya jika ada kuota (numeric) dan asal di Lombok
                                    if (is_numeric($kuotaPengeluaran) && (int)$value > (int)$kuotaPengeluaran) {
                                        $fail("Jumlah ternak ({$value}) melebihi kuota tersedia ({$kuotaPengeluaran}).");
                                    }
                                },
                            ])
                            ->required()
                            ->reactive()
                            ->columnSpanFull(),
                    ])->columns(),

                Forms\Components\Section::make('Dokumen')
                    ->schema([
                        Forms\Components\FileUpload::make('surat_permohonan')
                            ->label('Surat Permohonan Perusahaan')
                            ->disk('public')
                            ->directory('pengajuan/surat_permohonan')
                            ->acceptedFileTypes(['application/pdf']),

                        Forms\Components\TextInput::make('nomor_surat_permohonan')
                            ->label('Nomor Surat Permohonan Perusahaan')
                            ->required(),

                        Forms\Components\DatePicker::make('tanggal_surat_permohonan')
                            ->label('Tanggal Surat Permohonan Perusahaan')
                            ->required(),

                        // SKKH akan diupload oleh dinas kab/kota asal ternak
                        Forms\Components\TextInput::make('nomor_skkh')
                            ->label('No. SKKH')
                            ->required()
                            ->helperText('SKKH akan diupload oleh dinas kab/kota asal ternak')
                            ->hiddenOn('create'),

                        Forms\Components\FileUpload::make('hasil_uji_lab')
                            ->label('Hasil Uji Lab')
                            ->disk('public')
                            ->directory('pengajuan/hasil_uji_lab')
                            ->acceptedFileTypes(['application/pdf']),

                        Forms\Components\FileUpload::make('dokumen_lainnya')
                            ->label('Dokumen Lainnya (Jika Ada)')
                            ->disk('public')
                            ->directory('pengajuan/dokumen_lainnya')
                            ->acceptedFileTypes(['application/pdf']),
                    ]),
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
                Tables\Columns\TextColumn::make('user.nama_perusahaan')
                    ->label('Perusahaan/Instansi')
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('user', function ($q) use ($search) {
                            $q->where('nama_perusahaan', 'like', "%{$search}%")
                              ->orWhere('name', 'like', "%{$search}%");
                        });
                    })
                    ->formatStateUsing(fn($state, $record) => $state ?: ($record->user->name ?? '-')),
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
                    ->color(fn($state) => match ($state) {
                        'menunggu' => 'gray',
                        'disetujui' => 'success',
                        'ditolak' => 'danger',
                        'diproses' => 'warning',
                        'selesai' => 'success',
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            \App\Filament\Resources\PengajuanResource\RelationManagers\HistoriPengajuanRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengajuan::route('/'),
            'create' => Pages\CreatePengajuan::route('/create'),
            'view' => Pages\ViewPengajuan::route('/{record}'),
            'edit' => Pages\EditPengajuan::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        $count = PengajuanService::countPerluDitindaklanjutiFor($user, 'antar_kab_kota');
        return $count > 0 ? (string) $count : null;
    }

    public static function shouldCheckAccess(): bool
    {
        $user = auth()->user();
        
        if (!$user->provinsi_verified_at) {
            UnverifiedAccountNotification::make()->send();
            return false;
        }

        return true;
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();
        return $user->wewenang->nama === 'Pengguna' && $user->provinsi_verified_at !== null;
    }
}
