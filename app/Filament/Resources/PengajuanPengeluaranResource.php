<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Pengajuan;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Services\PengajuanService;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PengajuanPengeluaranResource\Pages;
use App\Filament\Components\UnverifiedAccountNotification;
use App\Services\PelabuhanService;

class PengajuanPengeluaranResource extends Resource
{
    protected static ?string $model = Pengajuan::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationLabel = 'Pengajuan Pengeluaran';
    protected static ?string $navigationGroup = 'Pengajuan';
    protected static ?int $navigationSort = 2;
    protected static ?string $slug = 'pengajuan-pengeluaran';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('jenis_pengajuan', 'pengeluaran');
    }

    public static function form(Form $form): Form
    {
        // Helper function untuk cek apakah jenis ternak adalah Bibit Sapi
        $isBibitSapi = function (callable $get) {
            $jenisTernakId = $get('jenis_ternak_id');
            if (!$jenisTernakId) {
                return false;
            }
            $jenisTernak = \App\Models\JenisTernak::find($jenisTernakId);
            return $jenisTernak && $jenisTernak->nama === 'Bibit Sapi';
        };

        $cekKuotaPengeluaran = function (callable $get) use ($isBibitSapi) {
            $tahun = $get('tahun_pengajuan');
            $jenisTernakId = $get('jenis_ternak_id');
            $kabKotaAsalId = $get('kab_kota_asal_id');
            $jenisKelamin = $get('jenis_kelamin');

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
            $isLombokAsal = $kabKotaAsal && in_array($kabKotaAsal->nama, $kabKotaLombok);

            if ($isLombokAsal) {
                // Untuk pulau Lombok, gunakan logika global
                return \App\Models\PenggunaanKuota::getKuotaTersisaLombok(
                    $tahun, $jenisTernakId, $jenisKelamin, 'pengeluaran'
                );
            } else {
                // Logika normal untuk kab/kota lain
                return \App\Models\PenggunaanKuota::getKuotaTersisa(
                    $tahun, $jenisTernakId, $kabKotaAsalId, $jenisKelamin, 'pengeluaran'
                );
            }
        };

        $cekKuotaJantan = function (callable $get) use ($cekKuotaPengeluaran) {
            return $cekKuotaPengeluaran(function($key) use ($get) {
                if ($key === 'jenis_kelamin') {
                    return 'jantan';
                }
                return $get($key);
            });
        };

        $cekKuotaBetina = function (callable $get) use ($cekKuotaPengeluaran) {
            return $cekKuotaPengeluaran(function($key) use ($get) {
                if ($key === 'jenis_kelamin') {
                    return 'betina';
                }
                return $get($key);
            });
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
                            ->relationship('kabKotaAsal', 'nama')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live(),

                        Forms\Components\Select::make('pelabuhan_asal')
                            ->label('Nama Pelabuhan Asal')
                            ->options(PelabuhanService::getPelabuhanOptionsWithLoading())
                            ->preload()
                            ->searchable()
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

                        Forms\Components\Select::make('provinsi_tujuan_id')
                            ->label('Provinsi Tujuan Ternak')
                            ->relationship('provinsiTujuan', 'nama', fn($query) => $query->where('nama', '!=', 'Nusa Tenggara Barat'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('kab_kota_tujuan')
                            ->label('Kota Tujuan Ternak')
                            ->required(),

                        Forms\Components\Select::make('pelabuhan_tujuan')
                            ->label('Nama Pelabuhan Tujuan')
                            ->options(PelabuhanService::getPelabuhanOptionsWithLoading())
                            ->preload()
                            ->searchable()
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
                            ->options(fn(callable $get) => \App\Models\JenisTernak::where('kategori_ternak_id', $get('kategori_ternak_id'))->pluck('nama', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Reset fields saat jenis ternak berubah
                                $set('jenis_kelamin', null);
                                $set('jumlah_ternak', null);
                                $set('jumlah_jantan', null);
                                $set('jumlah_betina', null);
                            }),

                        // Field jenis_kelamin - hanya muncul jika BUKAN Bibit Sapi
                        Forms\Components\Select::make('jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->options([
                                'jantan' => 'Jantan',
                                'betina' => 'Betina',
                            ])
                            ->required(fn(callable $get) => !$isBibitSapi($get))
                            ->visible(fn(callable $get) => !$isBibitSapi($get))
                            ->live(),

                        Forms\Components\TextInput::make('ras_ternak')
                            ->label('Ras Ternak')
                            ->required(),

                        // Info untuk Bibit Sapi
                        Forms\Components\Placeholder::make('info_bibit_sapi_help')
                            ->label('')
                            ->content(fn(callable $get) => $isBibitSapi($get) 
                                ? '💡 Anda dapat mengisi hanya jantan saja, hanya betina saja, atau keduanya sekaligus. Minimal salah satu harus diisi.' 
                                : '')
                            ->visible(fn(callable $get) => $isBibitSapi($get))
                            ->columnSpanFull(),

                        // Field jumlah_ternak - hanya muncul jika BUKAN Bibit Sapi
                        Forms\Components\TextInput::make('jumlah_ternak')
                            ->label('Jumlah Ternak')
                            ->numeric()
                            ->minValue(1)
                            ->helperText(fn(callable $get) => !$isBibitSapi($get) ? 'Kuota tersedia: ' . $cekKuotaPengeluaran($get) : '')
                            ->required(fn(callable $get) => !$isBibitSapi($get))
                            ->visible(fn(callable $get) => !$isBibitSapi($get))
                            ->reactive()
                            ->columnSpanFull(),

                        // Field jumlah_jantan dan jumlah_betina - hanya muncul jika Bibit Sapi
                        Forms\Components\TextInput::make('jumlah_jantan')
                            ->label('Jumlah Ternak Jantan')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->helperText(fn(callable $get) => $isBibitSapi($get) 
                                ? 'Kuota tersedia: ' . $cekKuotaJantan($get) . ' (Bisa hanya isi jantan saja atau bersama betina)' 
                                : '')
                            ->required(fn(callable $get) => false) // Tidak required karena bisa hanya betina
                            ->visible(fn(callable $get) => $isBibitSapi($get))
                            ->reactive()
                            ->rules([
                                fn (callable $get) => function (string $attribute, $value, \Closure $fail) use ($get, $isBibitSapi, $cekKuotaJantan) {
                                    if ($isBibitSapi($get)) {
                                        $jumlahJantan = (int)($value ?? 0);
                                        $jumlahBetina = (int)($get('jumlah_betina') ?? 0);
                                        
                                        // Validasi: minimal salah satu harus diisi
                                        if ($jumlahJantan == 0 && $jumlahBetina == 0) {
                                            $fail('Minimal salah satu (jantan atau betina) harus diisi.');
                                            return;
                                        }
                                        
                                        // Validasi: jumlah jantan tidak boleh melebihi kuota
                                        if ($jumlahJantan > 0) {
                                            $kuotaTersedia = $cekKuotaJantan($get);
                                            if ($jumlahJantan > $kuotaTersedia) {
                                                $fail("Jumlah jantan ({$jumlahJantan}) melebihi kuota tersedia ({$kuotaTersedia}).");
                                            }
                                        }
                                    }
                                },
                            ])
                            ->afterStateUpdated(function ($state, callable $set, callable $get) use ($isBibitSapi) {
                                if ($isBibitSapi($get)) {
                                    $jantan = (int)($state ?? 0);
                                    $betina = (int)($get('jumlah_betina') ?? 0);
                                    $set('jumlah_ternak', $jantan + $betina);
                                }
                            }),

                        Forms\Components\TextInput::make('jumlah_betina')
                            ->label('Jumlah Ternak Betina')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->helperText(fn(callable $get) => $isBibitSapi($get) 
                                ? 'Kuota tersedia: ' . $cekKuotaBetina($get) . ' (Bisa hanya isi betina saja atau bersama jantan)' 
                                : '')
                            ->required(fn(callable $get) => false) // Tidak required karena bisa hanya jantan
                            ->visible(fn(callable $get) => $isBibitSapi($get))
                            ->reactive()
                            ->rules([
                                fn (callable $get) => function (string $attribute, $value, \Closure $fail) use ($get, $isBibitSapi, $cekKuotaBetina) {
                                    if ($isBibitSapi($get)) {
                                        $jumlahJantan = (int)($get('jumlah_jantan') ?? 0);
                                        $jumlahBetina = (int)($value ?? 0);
                                        
                                        // Validasi: minimal salah satu harus diisi
                                        if ($jumlahJantan == 0 && $jumlahBetina == 0) {
                                            $fail('Minimal salah satu (jantan atau betina) harus diisi.');
                                            return;
                                        }
                                        
                                        // Validasi: jumlah betina tidak boleh melebihi kuota
                                        if ($jumlahBetina > 0) {
                                            $kuotaTersedia = $cekKuotaBetina($get);
                                            if ($jumlahBetina > $kuotaTersedia) {
                                                $fail("Jumlah betina ({$jumlahBetina}) melebihi kuota tersedia ({$kuotaTersedia}).");
                                            }
                                        }
                                    }
                                },
                            ])
                            ->afterStateUpdated(function ($state, callable $set, callable $get) use ($isBibitSapi) {
                                if ($isBibitSapi($get)) {
                                    $jantan = (int)($get('jumlah_jantan') ?? 0);
                                    $betina = (int)($state ?? 0);
                                    $set('jumlah_ternak', $jantan + $betina);
                                }
                            }),
                    ])->columns(),

                Forms\Components\Section::make('Dokumen')
                    ->schema([
                        Forms\Components\FileUpload::make('surat_permohonan')
                            ->label('Surat Permohonan Perusahaan')
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
                            ->acceptedFileTypes(['application/pdf']),

                        Forms\Components\FileUpload::make('dokumen_lainnya')
                            ->label('Dokumen Lainnya (Jika Ada)')
                            ->acceptedFileTypes(['application/pdf']),

                        Forms\Components\FileUpload::make('izin_ptsp_daerah')
                            ->label('Izin PTSP Daerah Penerima')
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
                Tables\Columns\TextColumn::make('provinsiTujuan.nama')
                    ->label('Provinsi Tujuan'),
                Tables\Columns\TextColumn::make('kab_kota_tujuan')
                    ->label('Kota Tujuan'),
                Tables\Columns\TextColumn::make('jenisTernak.nama')
                    ->label('Jenis Ternak'),
                Tables\Columns\TextColumn::make('jumlah_ternak')
                    ->label('Jumlah'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
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
            'index' => Pages\ListPengajuanPengeluaran::route('/'),
            'create' => Pages\CreatePengajuanPengeluaran::route('/create'),
            'view' => Pages\ViewPengajuanPengeluaran::route('/{record}'),
            'edit' => Pages\EditPengajuanPengeluaran::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = PengajuanService::countPerluDitindaklanjutiFor(auth()->user(), 'pengeluaran');
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