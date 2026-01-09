<?php

namespace App\Filament\Resources\Sp3Resource\Pages;

use App\Filament\Resources\Sp3Resource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;
use App\Models\Pengaturan;
use App\Helpers\FormatHelper;
use Filament\Notifications\Notification;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Form;
use Filament\Forms\Components\FileUpload;

class ViewSp3 extends ViewRecord
{
    protected static string $resource = Sp3Resource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Status Pendaftaran')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('is_pernah_daftar')
                                    ->label('Status Pendaftaran')
                                    ->formatStateUsing(fn($state) => $state ? 'Sudah Pernah Mendaftar' : 'Pendaftaran Baru')
                                    ->badge()
                                    ->color(fn($state) => $state ? 'success' : 'warning'),
                                TextEntry::make('no_sp3')
                                    ->label('No. SP3')
                                    ->visible(fn($record) => $record->is_pernah_daftar),
                                TextEntry::make('no_register')
                                    ->label('Nomor Register')
                                    ->visible(fn($record) => $record->is_pernah_daftar),
                                TextEntry::make('sp3')
                                    ->label('Dokumen SP3')
                                    ->formatStateUsing(function ($state, $record) {
                                        if (!$state)
                                            return 'Belum diunggah';
                                        return new \Illuminate\Support\HtmlString(
                                            view('filament.components.document-link', [
                                                'url' => Storage::url($state),
                                                'label' => 'Lihat Dokumen'
                                            ])->render()
                                        );
                                    })
                                    ->visible(fn($record) => $record->is_pernah_daftar),
                            ]),
                    ]),

                Section::make('Jenis Akun')
                    ->schema([
                        TextEntry::make('jenis_akun')
                            ->label('Jenis Akun')
                            ->formatStateUsing(fn($state) => match ($state) {
                                'perusahaan' => 'Perusahaan',
                                'perorangan' => 'Perorangan/Instansi Pemerintah',
                                default => '-'
                            }),
                    ]),

                Section::make('Data Perusahaan/Instansi')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('nama_perusahaan')
                                    ->label('Nama Perusahaan/Instansi'),
                                TextEntry::make('bidang_usaha')
                                    ->label('Bidang Usaha')
                                    ->formatStateUsing(fn($state) => match ($state) {
                                        'hewan_ternak' => 'Hewan Ternak',
                                        'hewan_kesayangan' => 'Hewan Kesayangan',
                                        'produk_hewan_produk_olahan' => 'Produk Hewan/Produk Olahan',
                                        'gabungan_di_antaranya' => 'Gabungan di Antaranya',
                                        default => '-'
                                    }),
                                TextEntry::make('akta_pendirian')
                                    ->label('Akta Pendirian')
                                    ->formatStateUsing(function ($state) {
                                        if (!$state)
                                            return 'Belum diunggah';
                                        return new \Illuminate\Support\HtmlString(
                                            view('filament.components.document-link', [
                                                'url' => Storage::url($state),
                                                'label' => 'Lihat Dokumen'
                                            ])->render()
                                        );
                                    }),
                                TextEntry::make('surat_domisili')
                                    ->label('Surat Domisili')
                                    ->formatStateUsing(function ($state) {
                                        if (!$state)
                                            return 'Belum diunggah';
                                        return new \Illuminate\Support\HtmlString(
                                            view('filament.components.document-link', [
                                                'url' => Storage::url($state),
                                                'label' => 'Lihat Dokumen'
                                            ])->render()
                                        );
                                    }),
                                TextEntry::make('nib')
                                    ->label('NIB (Nomor Induk Berusaha)')
                                    ->formatStateUsing(function ($state) {
                                        if (!$state)
                                            return 'Belum diunggah';
                                        return new \Illuminate\Support\HtmlString(
                                            view('filament.components.document-link', [
                                                'url' => Storage::url($state),
                                                'label' => 'Lihat Dokumen'
                                            ])->render()
                                        );
                                    }),
                                TextEntry::make('no_nib')
                                    ->label('Nomor NIB'),
                                TextEntry::make('npwp')
                                    ->label('NPWP')
                                    ->formatStateUsing(function ($state) {
                                        if (!$state)
                                            return 'Belum diunggah';
                                        return new \Illuminate\Support\HtmlString(
                                            view('filament.components.document-link', [
                                                'url' => Storage::url($state),
                                                'label' => 'Lihat Dokumen'
                                            ])->render()
                                        );
                                    }),
                                TextEntry::make('no_npwp')
                                    ->label('Nomor NPWP'),
                                TextEntry::make('rekomendasi_keswan')
                                    ->label('Rekomendasi Kab/Kota')
                                    ->formatStateUsing(function ($state) {
                                        if (!$state)
                                            return 'Belum diunggah';
                                        return new \Illuminate\Support\HtmlString(
                                            view('filament.components.document-link', [
                                                'url' => Storage::url($state),
                                                'label' => 'Lihat Dokumen'
                                            ])->render()
                                        );
                                    }),
                                TextEntry::make('surat_kandang_penampungan')
                                    ->label('Surat Keterangan Mempunyai Kandang Penampungan')
                                    ->formatStateUsing(function ($state) {
                                        if (!$state)
                                            return 'Belum diunggah';
                                        return new \Illuminate\Support\HtmlString(
                                            view('filament.components.document-link', [
                                                'url' => Storage::url($state),
                                                'label' => 'Lihat Dokumen'
                                            ])->render()
                                        );
                                    }),
                                TextEntry::make('surat_permohonan_perusahaan')
                                    ->label('Surat Permohonan Perusahaan')
                                    ->formatStateUsing(function ($state) {
                                        if (!$state)
                                            return 'Belum diunggah';
                                        return new \Illuminate\Support\HtmlString(
                                            view('filament.components.document-link', [
                                                'url' => Storage::url($state),
                                                'label' => 'Lihat Dokumen'
                                            ])->render()
                                        );
                                    }),
                            ]),
                    ])
                    ->visible(fn($record) => $record->jenis_akun === 'perusahaan'),

                Section::make('Data Pribadi')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Nama'),
                                TextEntry::make('email')
                                    ->label('Email'),
                                TextEntry::make('nik')
                                    ->label('NIK'),
                                TextEntry::make('desa')
                                    ->label('Desa'),
                                TextEntry::make('alamat')
                                    ->label('Alamat')
                                    ->columnSpanFull(),
                                TextEntry::make('telepon')
                                    ->label('Telepon/HP/Faximile'),
                            ]),
                    ]),

                Section::make('Dokumen Kabupaten/Kota')
                    ->visible(fn($record) => $record->kab_kota_verified_at)
                    ->schema([
                        TextEntry::make('rekomendasi_keswan')
                            ->label('Surat Rekomendasi Keswan')
                            ->formatStateUsing(function ($state) {
                                if (!$state)
                                    return 'Belum diunggah';
                                return new \Illuminate\Support\HtmlString(
                                    view('filament.components.document-link', [
                                        'url' => Storage::url($state),
                                        'label' => 'Lihat Dokumen'
                                    ])->render()
                                );
                            }),
                        TextEntry::make('surat_kandang_penampungan')
                            ->label('Surat Keterangan Kandang/Gudang')
                            ->formatStateUsing(function ($state) {
                                if (!$state)
                                    return 'Belum diunggah';
                                return new \Illuminate\Support\HtmlString(
                                    view('filament.components.document-link', [
                                        'url' => Storage::url($state),
                                        'label' => 'Lihat Dokumen'
                                    ])->render()
                                );
                            }),
                    ]),

                Section::make('Dokumen Pendukung')
                    ->schema([
                        TextEntry::make('dokumen_pendukung')
                            ->label('Dokumen Pendukung Lainnya')
                            ->formatStateUsing(function ($state) {
                                if (!$state)
                                    return 'Belum diunggah';
                                return new \Illuminate\Support\HtmlString(
                                    view('filament.components.document-link', [
                                        'url' => Storage::url($state),
                                        'label' => 'Lihat Dokumen'
                                    ])->render()
                                );
                            }),
                    ]),

                Section::make('Status Verifikasi')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('kab_kota_verified_at')
                                    ->label('Status Kab/Kota')
                                    ->formatStateUsing(fn($state) => $state ? 'Terverifikasi' : 'Belum Terverifikasi')
                                    ->badge()
                                    ->color(fn($state) => $state ? 'success' : 'warning'),
                                TextEntry::make('kab_kota_verified_at')
                                    ->label('Diverifikasi Kab/Kota Pada')
                                    ->formatStateUsing(fn($state) => $state ? $state->translatedFormat('d F Y H:i') : '-'),
                                TextEntry::make('kabKotaVerifiedBy.name')
                                    ->label('Diverifikasi Kab/Kota Oleh')
                                    ->formatStateUsing(fn($state) => $state ?: '-'),
                                TextEntry::make('provinsi_verified_at')
                                    ->label('Status Provinsi')
                                    ->formatStateUsing(fn($state) => $state ? 'Terverifikasi' : 'Belum Terverifikasi')
                                    ->badge()
                                    ->color(fn($state) => $state ? 'success' : 'warning'),
                                TextEntry::make('provinsi_verified_at')
                                    ->label('Diverifikasi Provinsi Pada')
                                    ->formatStateUsing(fn($state) => $state ? $state->translatedFormat('d F Y H:i') : '-'),
                                TextEntry::make('provinsiVerifiedBy.name')
                                    ->label('Diverifikasi Provinsi Oleh')
                                    ->formatStateUsing(fn($state) => $state ?: '-'),
                                TextEntry::make('provinsi_verified_at')
                                    ->label('Status Akun')
                                    ->formatStateUsing(fn($state) => $state ? 'Aktif' : 'Belum Aktif')
                                    ->badge()
                                    ->color(fn($state) => $state ? 'success' : 'danger'),
                                TextEntry::make('provinsi_verified_at')
                                    ->label('Aktif Pada')
                                    ->formatStateUsing(fn($state) => $state ? $state->translatedFormat('d F Y H:i') : '-'),
                                TextEntry::make('provinsiVerifiedBy.name')
                                    ->label('Diaktifkan Oleh')
                                    ->formatStateUsing(fn($state) => $state ?: '-'),
                                TextEntry::make('tanggal_verifikasi')
                                    ->label('Tanggal Verifikasi')
                                    ->formatStateUsing(fn($state) => $state ? $state->translatedFormat('d F Y H:i') : '-'),
                                TextEntry::make('tanggal_berlaku')
                                    ->label('Tanggal Berlaku')
                                    ->formatStateUsing(fn($state) => $state ? $state->translatedFormat('d F Y H:i') : '-'),
                            ]),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('draft_sp3')
                ->label('Draft SP3')
                ->icon('heroicon-o-document-arrow-down')
                ->visible(fn() => !$this->record->is_pernah_daftar && !$this->record->sp3)
                ->action(function () {
                    $template = new TemplateProcessor(storage_path('template/format-sp3.docx'));

                    $biodataKadis = json_decode(Pengaturan::firstWhere('key', 'biodata_kadis')->value);

                    $bidangUsaha = match ($this->record->bidang_usaha) {
                        'hewan_ternak' => 'Hewan Ternak',
                        'hewan_kesayangan' => 'Hewan Kesayangan',
                        'produk_hewan_produk_olahan' => 'Produk Hewan/Produk Olahan',
                        'gabungan_di_antaranya' => 'Gabungan di Antaranya',
                        default => '-'
                    };

                    // Tanggal untuk template - gunakan tanggal verifikasi provinsi jika sudah ada, jika belum gunakan tanggal sekarang
                    // Tanggal register sesuai dengan tanggal approve (tanggal verifikasi)
                    $tanggalVerifikasi = $this->record->tanggal_verifikasi ?? $this->record->provinsi_verified_at ?? now();
                    $tanggalBerlaku = $this->record->tanggal_berlaku;
                    if (!$tanggalBerlaku && $tanggalVerifikasi) {
                        $tanggalBerlaku = $tanggalVerifikasi->copy()->addYears(3);
                    }

                    // Ambil data dinas untuk tembusan berdasarkan kab/kota user
                    $kabKota = $this->record->kabKota;
                    $tembusan = '';
                    if ($kabKota && $kabKota->nama_dinas && $kabKota->alamat_dinas) {
                        $tembusan = $kabKota->nama_dinas . "\n" . $kabKota->alamat_dinas;
                    }

                    $template->setValues([
                        'nomor' => $this->record->no_sp3 ?? '-',
                        'nama_perusahaan' => $this->record->nama_perusahaan ?? '-',
                        'penanggung_jawab' => $this->record->name ?? '-',
                        'bidang_usaha' => $bidangUsaha,
                        'nomor_nib' => $this->record->no_nib ?? '-',
                        'nomor_tanda_daftar_perusahaan' => $this->record->no_nib ?? '-', // NIB untuk nomor tanda daftar perusahaan
                        'nomor_surat_izin_usaha' => $this->record->no_nib ?? $this->record->no_npwp ?? '-', // Surat Izin Usaha (bisa NIB atau NPWP)
                        'nomor_npwp' => $this->record->no_npwp ?? '-',
                        'alamat' => str($this->record->alamat ?? '')->title()->toString() . ' ' . ($this->record->kabKota?->nama ?? ''),
                        'telpon' => $this->record->telepon ?? '-',
                        'nomor_register' => $this->record->no_register ?? '-',
                        'tanggal_register' => $tanggalVerifikasi ? $tanggalVerifikasi->translatedFormat('d F Y') : '-',
                        'berlaku_hingga' => $tanggalBerlaku ? $tanggalBerlaku->translatedFormat('d F Y') : '-',
                        'tanggal_ttd' => $tanggalVerifikasi ? $tanggalVerifikasi->translatedFormat('d F Y') : '-',
                        'nama_kadis' => $biodataKadis->nama ?? '-',
                        'pangkat_kadis' => $biodataKadis->jabatan ?? '-',
                        'nip_kadis' => $biodataKadis->nip ?? '-',
                        'kabupaten' => $this->record->kabKota?->nama ?? '-',
                        'kel_desa' => $this->record->desa ?? '-',
                        'tembusan' => $tembusan
                    ]);

                    $outputPath = storage_path('template/hasil-sp3.docx');
                    $template->saveAs($outputPath);

                    return response()->download($outputPath)->deleteFileAfterSend();
                }),
            Action::make('lihat_sp3')
                ->label('Lihat SP3')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->visible(fn() => $this->record->sp3)
                ->url(fn() => Storage::url($this->record->sp3))
                ->openUrlInNewTab(),
            Action::make('verify_kab_kota')
                ->label('Verifikasi Kab/Kota')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(function() {
                    $user = auth()->user();
                    // Skip kab/kota verification for users who have registered before
                    if ($this->record->is_pernah_daftar) {
                        return false;
                    }
                    if ($user->wewenang->nama === 'Administrator' && !$this->record->kab_kota_verified_at) {
                        return true;
                    }
                    return $user->wewenang->nama === 'Disnak Kab/Kota' &&
                           $user->kab_kota_id === $this->record->kab_kota_id &&
                           !$this->record->kab_kota_verified_at &&
                           $this->record->wewenang->nama === 'Pengguna';
                })
                ->form([
                    FileUpload::make('rekomendasi_keswan')
                        ->label('Surat Rekomendasi Keswan')
                        ->required()
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->maxSize(10240) // 10MB
                        ->directory('rekomendasi-keswan')
                        ->visibility('private')
                        ->downloadable()
                        ->openable()
                        ->previewable(false)
                        ->helperText('Upload surat rekomendasi keswan (PDF, JPG, PNG - maksimal 10MB)'),
                    FileUpload::make('surat_kandang_penampungan')
                        ->label('Surat Keterangan Kandang/Gudang')
                        ->required()
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->maxSize(10240) // 10MB
                        ->directory('surat-kandang')
                        ->visibility('private')
                        ->downloadable()
                        ->openable()
                        ->previewable(false)
                        ->helperText('Upload surat keterangan kandang/gudang (PDF, JPG, PNG - maksimal 10MB)'),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'kab_kota_verified_at' => now(),
                        'kab_kota_verified_by' => auth()->id(),
                        'rekomendasi_keswan' => $data['rekomendasi_keswan'],
                        'surat_kandang_penampungan' => $data['surat_kandang_penampungan'],
                    ]);

                    Notification::make()
                        ->title('User berhasil diverifikasi oleh Kab/Kota')
                        ->success()
                        ->send();
                }),
            Action::make('verify_provinsi')
                ->label('Verifikasi Provinsi')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(function() {
                    $user = auth()->user();
                    // Allow direct provinsi verification for users who have registered before
                    if ($this->record->is_pernah_daftar) {
                        if ($user->wewenang->nama === 'Administrator' && !$this->record->provinsi_verified_at) {
                            return true;
                        }
                        return $user->wewenang->nama === 'Disnak Provinsi' &&
                               !$this->record->provinsi_verified_at &&
                               $this->record->wewenang->nama === 'Pengguna';
                    }
                    // For new users, require kab/kota verification first
                    if ($user->wewenang->nama === 'Administrator' && $this->record->kab_kota_verified_at && !$this->record->provinsi_verified_at) {
                        return true;
                    }
                    return $user->wewenang->nama === 'Disnak Provinsi' &&
                           $this->record->kab_kota_verified_at &&
                           !$this->record->provinsi_verified_at &&
                           $this->record->wewenang->nama === 'Pengguna';
                })
                ->form([
                    TextInput::make('no_sp3')
                        ->label('No. SP3')
                        ->required(fn($record) => !$record->is_pernah_daftar)
                        ->default(fn($record) => $record->no_sp3)
                        ->maxLength(255),
                    TextInput::make('no_register')
                        ->label('Nomor Register')
                        ->required(fn($record) => !$record->is_pernah_daftar)
                        ->default(fn($record) => $record->no_register)
                        ->maxLength(255),
                    FileUpload::make('sp3')
                        ->label('Dokumen SP3')
                        ->required(fn($record) => !$record->is_pernah_daftar)
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->maxSize(10240) // 10MB
                        ->directory('sp3-documents')
                        ->visibility('private')
                        ->downloadable()
                        ->openable()
                        ->previewable(false)
                        ->helperText('Upload dokumen SP3 yang sudah ditandatangani (PDF, JPG, PNG - maksimal 10MB)'),
                ])
                ->action(function (array $data) {
                    $now = now();
                    $updateData = [
                        'provinsi_verified_at' => $now,
                        'provinsi_verified_by' => auth()->id(),
                        'tanggal_verifikasi' => $now,
                        'tanggal_berlaku' => $now->copy()->addYears(3),
                        'no_sp3' => $data['no_sp3'],
                        'no_register' => $data['no_register']
                    ];

                    // Only update SP3 document if it's a new registration or if a new document is uploaded
                    if (!$this->record->is_pernah_daftar || !empty($data['sp3'])) {
                        $updateData['sp3'] = $data['sp3'];
                    }

                    $this->record->update($updateData);

                    Notification::make()
                        ->title('User berhasil diverifikasi oleh Provinsi')
                        ->success()
                        ->send();
                }),
        ];
    }
}