<?php

namespace App\Filament\Resources\PengajuanResource\Pages;

use Filament\Actions;
use Filament\Infolists\Infolist;
use App\Services\PengajuanService;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Resources\PengajuanResource;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use App\Models\DokumenPengajuan;
use Filament\Notifications\Notification;

class ViewPengajuan extends ViewRecord
{
    protected static string $resource = PengajuanResource::class;

    protected static ?string $title = 'Detail Pengajuan';

    public function mount(int | string $record): void
    {
        parent::mount($record);
        
        // Proteksi akses berdasarkan bidang
        $user = auth()->user();
        $record = $this->record;
        
        // Admin bisa akses semua
        if ($user->is_admin) {
            return;
        }
        
        // Pengguna hanya bisa akses pengajuan miliknya
        if ($user->wewenang->nama === 'Pengguna') {
            if ($record->user_id !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
            }
            return;
        }
        
        // Disnak Provinsi: HARUS punya bidang_id dan hanya bisa akses pengajuan sesuai bidangnya
        if ($user->wewenang->nama === 'Disnak Provinsi') {
            if (!$user->bidang_id) {
                abort(403, 'Akun Anda belum dikaitkan dengan bidang. Silakan hubungi administrator.');
            }
            $pengajuanBidangId = $record->jenisTernak?->bidang_id;
            if ($pengajuanBidangId !== $user->bidang_id) {
                abort(403, 'Anda tidak memiliki akses ke pengajuan dari bidang lain.');
            }
            return;
        }
        
        // Disnak Kab/Kota: hanya bisa akses pengajuan yang asal/tujuan kab/kotanya
        if ($user->wewenang->nama === 'Disnak Kab/Kota') {
            $isKabKotaAsal = $record->kab_kota_asal_id === $user->kab_kota_id;
            $isKabKotaTujuan = $record->kab_kota_tujuan_id === $user->kab_kota_id;
            if (!$isKabKotaAsal && !$isKabKotaTujuan) {
                abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
            }
            return;
        }
        
        // DPMPTSP: hanya bisa akses pengajuan yang sudah disetujui/selesai
        if ($user->wewenang->nama === 'DPMPTSP') {
            if (!in_array($record->status, ['disetujui', 'selesai'])) {
                abort(403, 'Anda hanya bisa mengakses pengajuan yang sudah disetujui atau selesai.');
            }
            return;
        }
    }

    public function getContentTabIcon(): ?string
    {
        return 'heroicon-m-eye';
    }

    public function getContentTabLabel(): ?string
    {
        return 'Detail Pengajuan';
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getHeaderActions(): array
    {
        $user = auth()->user();
        return [
            Actions\Action::make('verifikasi')
                ->label('Verifikasi')
                ->icon('heroicon-o-check')
                ->color('info')
                ->visible(fn($record) => $record->canVerifyBy($user))
                ->disabled(fn($record) => $record->is_kuota_penuh)
                ->tooltip(fn($record) => $record->is_kuota_penuh ? 'Kuota sudah penuh - tidak dapat diverifikasi' : null)
                ->form([
                    Textarea::make('catatan')
                        ->label('Catatan')
                        ->required(),
                ])
                ->action(fn(array $data) => PengajuanService::verifikasi($this->record, auth()->user(), $data)),
            Actions\Action::make('tolak')
                ->label('Tolak')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->visible(fn($record) => $record->canRejectBy($user))
                ->form([
                    Textarea::make('alasan_penolakan')
                        ->label('Alasan Penolakan')
                        ->required()
                        ->default(function () {
                            $record = $this->record;
                            return $record->is_kuota_penuh ? 'Kuota sudah penuh' : null;
                        }),
                ])
                ->action(fn(array $data) => PengajuanService::tolak($this->record, auth()->user(), $data)),
            Actions\Action::make('ajukan_kembali')
                ->label('Ajukan Kembali')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->visible(fn($record) => $record->status === 'ditolak' && $user->id === $record->user_id)
                ->form([
                    Textarea::make('catatan')
                        ->label('Catatan Pengajuan Kembali')
                        ->required(),
                ])
                ->action(fn(array $data) => PengajuanService::ajukanKembali($this->record, auth()->user(), $data)),
            
            // Action untuk upload dokumen
            Actions\Action::make('upload_dokumen')
                ->label('Upload Dokumen')
                ->icon('heroicon-o-document-plus')
                ->color('info')
                ->visible(fn($record) => $this->canUploadDocument($record, $user))
                ->form([
                    Select::make('jenis_dokumen')
                        ->label('Jenis Dokumen')
                        ->options(fn($record) => $this->getAvailableDocumentTypes($record, $user))
                        ->required(),
                    
                    FileUpload::make('path_file')
                        ->label('File Dokumen')
                        ->disk('public')
                        ->directory('dokumen-pengajuan')
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->maxSize(function ($get) {
                            $jenisDokumen = $get('jenis_dokumen');
                            // 500KB untuk permohonan dan rekomendasi
                            if (in_array($jenisDokumen, ['rekomendasi_keswan'])) {
                                return 512; // 500KB
                            }
                            // 5MB untuk dokumen lainnya
                            return 5120; // 5MB
                        })
                        ->visibility('private')
                        ->required()
                        ->helperText(function ($get) {
                            $jenisDokumen = $get('jenis_dokumen');
                            if (in_array($jenisDokumen, ['rekomendasi_keswan'])) {
                                return 'Maksimal ukuran file: 500KB';
                            }
                            return 'Maksimal ukuran file: 5MB';
                        }),
                    
                    TextInput::make('keterangan')
                        ->label('Keterangan')
                        ->maxLength(500),
                ])
                ->action(function (array $data) {
                    $file = $data['path_file'];
                    $filePath = is_array($file) ? $file[0] : $file;
                    
                    // Cek apakah sudah ada dokumen dengan jenis yang sama dari user yang sama untuk pengajuan ini
                    // Jika ada, nonaktifkan dokumen lama terlebih dahulu
                    $dokumenLama = DokumenPengajuan::where('pengajuan_id', $this->record->id)
                        ->where('user_id', auth()->id())
                        ->where('jenis_dokumen', $data['jenis_dokumen'])
                        ->where('status', 'aktif')
                        ->get();
                    
                    if ($dokumenLama->isNotEmpty()) {
                        // Nonaktifkan dokumen lama
                        foreach ($dokumenLama as $doc) {
                            $doc->update(['status' => 'tidak_aktif']);
                        }
                    }
                    
                    DokumenPengajuan::create([
                        'pengajuan_id' => $this->record->id,
                        'user_id' => auth()->id(),
                        'jenis_dokumen' => $data['jenis_dokumen'],
                        'nama_file' => $filePath,
                        'path_file' => $filePath,
                        'ukuran_file' => filesize(storage_path('app/public/' . $filePath)),
                        'tipe_file' => pathinfo($filePath, PATHINFO_EXTENSION),
                        'keterangan' => $data['keterangan'] ?? null,
                        'status' => 'aktif',
                    ]);
                    
                    Notification::make()
                        ->title('Dokumen berhasil diupload')
                        ->success()
                        ->send();
                }),
            
            // Action untuk generate ulang dokumen (untuk admin atau jika dokumen belum ada)
            Actions\Action::make('generate_dokumen')
                ->label('Generate Dokumen')
                ->icon('heroicon-o-document-arrow-down')
                ->color('warning')
                ->visible(fn($record) => 
                    ($user->is_admin || $user->wewenang->nama === 'Disnak Provinsi') && 
                    $record->status === 'disetujui'
                )
                ->requiresConfirmation()
                ->modalHeading('Generate Dokumen Otomatis')
                ->modalDescription('Apakah Anda yakin ingin meng-generate dokumen untuk pengajuan ini? Dokumen yang sudah ada tidak akan dihapus.')
                ->action(function () {
                    try {
                        $dokumenYangDiGenerate = \App\Services\PengajuanService::generateDokumenOtomatis($this->record);
                        $count = count($dokumenYangDiGenerate);
                        
                        Notification::make()
                            ->title($count > 0 ? "Berhasil generate {$count} dokumen" : 'Tidak ada dokumen yang di-generate')
                            ->body($count > 0 
                                ? "Dokumen telah di-generate dan siap untuk di-download." 
                                : 'Pastikan user Disnak Kab/Kota dan Provinsi sudah terdaftar untuk pengajuan ini.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal generate dokumen')
                            ->body('Error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            
            Actions\EditAction::make()
                ->visible(fn($record) => ($user->id === $record->user_id && in_array($record->status, ['menunggu', 'ditolak'])) || $user->is_admin),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Perusahaan/Instansi')
                    ->schema([
                        TextEntry::make('user.nama_perusahaan')
                            ->label('Nama Perusahaan/Instansi')
                            ->formatStateUsing(fn($state, $record) => $state ?: ($record->user->name ?? '-'))
                            ->default('-'),
                        TextEntry::make('user.name')
                            ->label('Nama Pengaju')
                            ->visible(fn($record) => $record->user->nama_perusahaan),
                        TextEntry::make('user.email')
                            ->label('Email'),
                        TextEntry::make('user.no_hp')
                            ->label('No. Telepon'),
                    ])->columns(2),

                Section::make()
                    ->schema([
                        TextEntry::make('status')
                            ->label('Status Pengajuan')
                            ->badge()
                            ->color(fn($record) => $record->status_proses_color),
                        TextEntry::make('status_proses_label')
                            ->label('Status Proses')
                            ->badge()
                            ->color(fn($record) => $record->status_proses_color),
                        TextEntry::make('kuota_tersedia')
                            ->label('Kuota Tersedia')
                            ->badge()
                            ->color(function($record) {
                                $perluKuota = false;
                                if ($record->jenis_pengajuan === 'pengeluaran') {
                                    $perluKuota = \App\Models\PenggunaanKuota::isKuotaRequired($record->jenis_ternak_id, 'pengeluaran');
                                } elseif ($record->jenis_pengajuan === 'pemasukan') {
                                    $perluKuota = \App\Models\PenggunaanKuota::isKuotaRequired($record->jenis_ternak_id, 'pemasukan', $record->kab_kota_tujuan_id);
                                } elseif ($record->jenis_pengajuan === 'antar_kab_kota') {
                                    $perluKuota = \App\Models\PenggunaanKuota::isKuotaRequired($record->jenis_ternak_id, 'pengeluaran');
                                }
                                
                                if (!$perluKuota) {
                                    return 'gray';
                                }
                                return $record->is_kuota_penuh ? 'danger' : 'success';
                            })
                            ->formatStateUsing(function($record) {
                                $perluKuota = false;
                                if ($record->jenis_pengajuan === 'pengeluaran') {
                                    $perluKuota = \App\Models\PenggunaanKuota::isKuotaRequired($record->jenis_ternak_id, 'pengeluaran');
                                } elseif ($record->jenis_pengajuan === 'pemasukan') {
                                    $perluKuota = \App\Models\PenggunaanKuota::isKuotaRequired($record->jenis_ternak_id, 'pemasukan', $record->kab_kota_tujuan_id);
                                } elseif ($record->jenis_pengajuan === 'antar_kab_kota') {
                                    // Untuk antar kab/kota, cek apakah perlu kuota pengeluaran dari asal atau pemasukan ke tujuan
                                    $kabKotaAsal = $record->kabKotaAsal;
                                    $kabKotaTujuan = $record->kabKotaTujuan;
                                    
                                    $kabKotaLombok = [
                                        'Kota Mataram',
                                        'Kab. Lombok Barat', 
                                        'Kab. Lombok Tengah',
                                        'Kab. Lombok Timur',
                                        'Kab. Lombok Utara'
                                    ];
                                    
                                    $isLombokAsal = $kabKotaAsal && in_array($kabKotaAsal->nama, $kabKotaLombok);
                                    $isLombokTujuan = $kabKotaTujuan && in_array($kabKotaTujuan->nama, $kabKotaLombok);
                                    
                                    // Cek kuota pengeluaran dari asal (jika asal dari Lombok)
                                    if ($isLombokAsal) {
                                        $perluKuota = \App\Models\PenggunaanKuota::isKuotaRequired($record->jenis_ternak_id, 'pengeluaran');
                                    }
                                    
                                    // Cek kuota pemasukan ke tujuan (jika tujuan ke Lombok dan asal bukan Lombok)
                                    if (!$isLombokAsal && $isLombokTujuan && $record->kab_kota_tujuan_id) {
                                        $perluKuotaPemasukan = \App\Models\PenggunaanKuota::isKuotaRequired($record->jenis_ternak_id, 'pemasukan', $record->kab_kota_tujuan_id);
                                        if ($perluKuotaPemasukan) {
                                            $perluKuota = true;
                                        }
                                    }
                                }
                                
                                if (!$perluKuota) {
                                    return 'Tidak ada kuota';
                                }
                                
                                if ($record->is_kuota_penuh) {
                                    return 'Tidak ada kuota';
                                }
                                
                                // Untuk pengajuan antar kab/kota, tampilkan kuota sesuai dengan logika:
                                // - Jika asal dari Lombok: kuota pengeluaran dari Lombok
                                // - Jika asal dari Sumbawa dan tujuan ke Lombok: kuota pemasukan ke tujuan
                                $kabKotaAsal = $record->kabKotaAsal;
                                $kabKotaTujuan = $record->kabKotaTujuan;
                                
                                // Daftar kab/kota di pulau Lombok
                                $kabKotaLombok = [
                                    'Kota Mataram',
                                    'Kab. Lombok Barat', 
                                    'Kab. Lombok Tengah',
                                    'Kab. Lombok Timur',
                                    'Kab. Lombok Utara'
                                ];
                                
                                $isLombokAsal = $kabKotaAsal && in_array($kabKotaAsal->nama, $kabKotaLombok);
                                $isLombokTujuan = $kabKotaTujuan && in_array($kabKotaTujuan->nama, $kabKotaLombok);
                                
                                // Jika kuota_tersedia adalah PHP_INT_MAX atau sangat besar, berarti tidak ada batasan kuota
                                // PHP_INT_MAX biasanya 9.223.372.036.854.775.807 (64-bit) atau 2.147.483.647 (32-bit)
                                // Cek jika nilai lebih besar dari 1 triliun, kemungkinan adalah PHP_INT_MAX
                                if ($record->kuota_tersedia > 1000000000000 || $record->kuota_tersedia <= 0) {
                                    if ($record->jenis_pengajuan === 'pengeluaran' && !$isLombokAsal && $kabKotaAsal) {
                                        return 'Bebas keluar (Pengeluaran - ' . $kabKotaAsal->nama . ')';
                                    }
                                    if ($record->jenis_pengajuan === 'antar_kab_kota' && !$isLombokAsal && !$isLombokTujuan) {
                                        return 'Bebas (Antar Kab/Kota)';
                                    }
                                    return 'Tidak ada kuota';
                                }
                                
                                $kuotaFormatted = $this->formatAngkaKuota($record->kuota_tersedia);
                                
                                // Untuk pengajuan antar kab/kota
                                if ($record->jenis_pengajuan === 'antar_kab_kota') {
                                    if ($isLombokAsal) {
                                        // Dari Lombok: tampilkan kuota pengeluaran
                                        return $kuotaFormatted . ' ekor (Pengeluaran - Pulau Lombok)';
                                    } elseif ($isLombokTujuan && $kabKotaTujuan) {
                                        // Dari Sumbawa ke Lombok: tampilkan kuota pemasukan ke tujuan
                                        return $kuotaFormatted . ' ekor (Pemasukan - ' . $kabKotaTujuan->nama . ')';
                                    } else {
                                        // Dari Sumbawa ke Sumbawa: tampilkan kuota pengeluaran dari asal (jika ada)
                                        return $kuotaFormatted . ' ekor (Pengeluaran - ' . ($kabKotaAsal ? $kabKotaAsal->nama : 'Tidak Diketahui') . ')';
                                    }
                                }
                                
                                // Untuk pengajuan pengeluaran/pemasukan biasa
                                if ($isLombokAsal) {
                                    return $kuotaFormatted . ' ekor (Pengeluaran - Pulau Lombok)';
                                } else {
                                    return $kuotaFormatted . ' ekor (Pengeluaran - ' . $record->kabKotaAsal->nama . ')';
                                }
                            }),
                        TextEntry::make('jumlah_ternak')
                            ->label('Jumlah Komoditas yang Diajukan')
                            ->badge()
                            ->color('info'),
                    ])->columns(2),
                Section::make('Informasi Pengajuan')
                    ->schema([
                        TextEntry::make('nomor_surat_permohonan')->label('Nomor Surat Permohonan'),
                        TextEntry::make('tanggal_surat_permohonan')->label('Tanggal Surat Permohonan')->date(),
                        TextEntry::make('kategoriTernak.nama')->label('Kategori Komoditas'),
                        TextEntry::make('jenisTernak.nama')->label('Jenis Komoditas'),
                        TextEntry::make('ras_ternak')->label('Ras/Strain/Nama Produk'),
                        TextEntry::make('jenis_kelamin')->label('Jenis Kelamin'),
                        TextEntry::make('kabKotaAsal.nama')->label('Kab/Kota Asal'),
                        TextEntry::make('pelabuhan_asal')->label('Pelabuhan Asal'),
                        TextEntry::make('kabKotaTujuan.nama')->label('Kab/Kota Tujuan'),
                        TextEntry::make('pelabuhan_tujuan')->label('Pelabuhan Tujuan'),
                        TextEntry::make('tahapVerifikasi.nama')->label('Tahap Verifikasi Saat Ini'),
                    ])->columns(2),
                Section::make('Dokumen')
                    ->schema([
                        TextEntry::make('surat_permohonan')
                            ->label('Surat Permohonan Perusahaan')
                            ->formatStateUsing(fn($state) => $state ? '<a href="' . asset('storage/' . $state) . '" target="_blank" class="text-primary-600 dark:text-primary-400">Lihat Dokumen</a>' : '')
                            ->html()
                            ->visible(fn($state) => $state),
                        TextEntry::make('nomor_surat_permohonan')->label('Nomor Surat Permohonan'),
                        TextEntry::make('tanggal_surat_permohonan')->label('Tanggal Surat Permohonan')->date(),
                        TextEntry::make('skkh')
                            ->label('SKKH')
                            ->formatStateUsing(fn($state) => $state ? '<a href="' . asset('storage/' . $state) . '" target="_blank" class="text-primary-600 dark:text-primary-400">Lihat Dokumen</a>' : '')
                            ->html()
                            ->visible(fn($state) => $state),
                        TextEntry::make('nomor_skkh')->label('No. SKKH'),
                        TextEntry::make('hasil_uji_lab')
                            ->label('Hasil Uji Lab')
                            ->formatStateUsing(fn($state) => $state ? '<a href="' . asset('storage/' . $state) . '" target="_blank" class="text-primary-600 dark:text-primary-400">Lihat Dokumen</a>' : '')
                            ->html()
                            ->visible(fn($state) => $state),
                        TextEntry::make('dokumen_lainnya')
                            ->label('Dokumen Lainnya')
                            ->formatStateUsing(fn($state) => $state ? '<a href="' . asset('storage/' . $state) . '" target="_blank" class="text-primary-600 dark:text-primary-400">Lihat Dokumen</a>' : '')
                            ->html()
                            ->visible(fn($state) => $state),
                    ])->columns(2),
                
                // Section untuk dokumen yang diupload oleh dinas
                Section::make('Dokumen dari Dinas')
                    ->schema([
                        TextEntry::make('dokumen_pengajuan')
                            ->label('Dokumen')
                            ->formatStateUsing(function ($record) {
                                $dokumen = $record->dokumenPengajuan()->aktif()->get();
                                if ($dokumen->isEmpty()) {
                                    return 'Belum ada dokumen yang diupload';
                                }
                                
                                $html = '<div class="space-y-2">';
                                foreach ($dokumen as $doc) {
                                    $html .= '<div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-800 rounded">';
                                    $html .= '<div>';
                                    $html .= '<span class="font-medium">' . $doc->nama_file_display . '</span><br>';
                                    $html .= '<span class="text-sm text-gray-500">Uploaded by: ' . $doc->user->name . '</span>';
                                    $html .= '</div>';
                                    $html .= '<a href="' . $doc->url_download . '" target="_blank" class="text-primary-600 dark:text-primary-400 hover:underline">Download</a>';
                                    $html .= '</div>';
                                }
                                $html .= '</div>';
                                
                                return $html;
                            })
                            ->html(),
                    ])
                    ->visible(fn($record) => $record->dokumenPengajuan()->aktif()->exists()),
            ]);
    }

    /**
     * Cek apakah user bisa upload dokumen
     */
    private function canUploadDocument($record, $user): bool
    {
        // Admin bisa upload kapan saja
        if ($user->is_admin) {
            return true;
        }

        // Cek berdasarkan wewenang dan status pengajuan
        if ($user->wewenang->nama === 'Disnak Kab/Kota') {
            // Dinas kab/kota bisa upload setelah pengajuan disetujui
            return in_array($record->status, ['disetujui', 'selesai']);
        }

        if ($user->wewenang->nama === 'Disnak Provinsi') {
            // Dinas provinsi bisa upload setelah pengajuan disetujui
            return in_array($record->status, ['disetujui', 'selesai']);
        }

        if ($user->wewenang->nama === 'DPMPTSP') {
            // DPMPTSP bisa upload setelah verifikasi
            return $record->status === 'disetujui';
        }

        return false;
    }

    /**
     * Get jenis dokumen yang bisa diupload berdasarkan user dan status
     */
    private function getAvailableDocumentTypes($record, $user): array
    {
        $types = [];

        if ($user->wewenang->nama === 'Disnak Kab/Kota') {
            if ($record->jenis_pengajuan === 'pengeluaran') {
                // Untuk pengeluaran, kab/kota asal upload semua dokumen
                if ($user->kab_kota_id === $record->kab_kota_asal_id) {
                    $types = [
                        'rekomendasi_keswan' => 'Rekomendasi Keswan',
                        'skkh' => 'SKKH',
                        'surat_keterangan_veteriner' => 'Surat Keterangan Veteriner',
                        'dokumen_lainnya' => 'Dokumen Lainnya',
                    ];
                }
            } elseif ($record->jenis_pengajuan === 'pemasukan') {
                // Untuk pemasukan, kab/kota tujuan upload rekomendasi saja
                if ($user->kab_kota_id === $record->kab_kota_tujuan_id) {
                    $types = [
                        'rekomendasi_keswan' => 'Rekomendasi Keswan',
                    ];
                }
            } else {
                // Antar kab/kota
                if ($user->kab_kota_id === $record->kab_kota_asal_id) {
                    // Kab/kota asal upload semua dokumen
                    $types = [
                        'rekomendasi_keswan' => 'Rekomendasi Keswan',
                        'skkh' => 'SKKH',
                        'surat_keterangan_veteriner' => 'Surat Keterangan Veteriner',
                        'dokumen_lainnya' => 'Dokumen Lainnya',
                    ];
                } elseif ($user->kab_kota_id === $record->kab_kota_tujuan_id) {
                    // Kab/kota tujuan upload rekomendasi saja
                    $types = [
                        'rekomendasi_keswan' => 'Rekomendasi Keswan',
                    ];
                }
            }
        }

        if ($user->wewenang->nama === 'Disnak Provinsi') {
            // Provinsi upload rekomendasi saja
            $types = [
                'rekomendasi_keswan' => 'Rekomendasi Keswan',
            ];
        }

        if ($user->wewenang->nama === 'DPMPTSP') {
            // DPMPTSP upload izin
            if ($record->jenis_pengajuan === 'pengeluaran') {
                $types = [
                    'izin_pengeluaran' => 'Izin Pengeluaran',
                ];
            } elseif ($record->jenis_pengajuan === 'pemasukan') {
                $types = [
                    'izin_pemasukan' => 'Izin Pemasukan',
                ];
            } else {
                $types = [
                    'izin_pengeluaran' => 'Izin Pengeluaran',
                    'izin_pemasukan' => 'Izin Pemasukan',
                ];
            }
        }

        return $types;
    }

    /**
     * Format angka kuota agar mudah dibaca
     */
    private function formatAngkaKuota($angka): string
    {
        if ($angka >= 1000000) {
            return number_format($angka / 1000000, 1, ',', '.') . 'M';
        } elseif ($angka >= 1000) {
            return number_format($angka / 1000, 1, ',', '.') . 'K';
        } else {
            return number_format($angka, 0, ',', '.');
        }
    }
}
