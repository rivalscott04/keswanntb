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
                        ->maxSize(10240) // 10MB
                        ->visibility('private')
                        ->required(),
                    
                    TextInput::make('keterangan')
                        ->label('Keterangan')
                        ->maxLength(500),
                ])
                ->action(function (array $data) {
                    $file = $data['path_file'];
                    $filePath = is_array($file) ? $file[0] : $file;
                    
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
            
            Actions\EditAction::make()
                ->visible(fn($record) => ($user->id === $record->user_id && in_array($record->status, ['menunggu', 'ditolak'])) || $user->is_admin),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
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
                            ->color(fn($record) => $record->is_kuota_penuh ? 'danger' : 'success')
                            ->formatStateUsing(function($record) {
                                if ($record->is_kuota_penuh) {
                                    return 'Kuota Penuh';
                                }
                                // Untuk pengajuan antar kab/kota, tampilkan kuota pengeluaran dari asal
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
                                
                                if ($isLombokAsal) {
                                    return $record->kuota_tersedia . ' ekor (Pengeluaran - Pulau Lombok)';
                                } else {
                                    return $record->kuota_tersedia . ' ekor (Pengeluaran - ' . $record->kabKotaAsal->nama . ')';
                                }
                            }),
                        TextEntry::make('jumlah_ternak')
                            ->label('Jumlah Ternak yang Diajukan')
                            ->badge()
                            ->color('info'),
                    ])->columns(2),
                Section::make('Informasi Pengajuan')
                    ->schema([
                        TextEntry::make('nomor_surat_permohonan')->label('Nomor Surat Permohonan'),
                        TextEntry::make('tanggal_surat_permohonan')->label('Tanggal Surat Permohonan')->date(),
                        TextEntry::make('kategoriTernak.nama')->label('Kategori Ternak'),
                        TextEntry::make('jenisTernak.nama')->label('Jenis Ternak'),
                        TextEntry::make('ras_ternak')->label('Ras Ternak'),
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
}
