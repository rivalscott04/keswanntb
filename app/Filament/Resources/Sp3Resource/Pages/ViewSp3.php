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
                        Grid::make(2)
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
                            ]),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download_sp3')
                ->label('Download SP3')
                ->icon('heroicon-o-document-arrow-down')
                ->visible(fn() => !$this->record->is_pernah_daftar)
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

                    $template->setValues([
                        'nomor' => $this->record->no_sp3,
                        'nama_perusahaan' => $this->record->nama_perusahaan,
                        'penanggung_jawab' => $this->record->name,
                        'bidang_usaha' => $bidangUsaha,
                        'nomor_nib' => $this->record->no_nib,
                        'nomor_npwp' => $this->record->no_npwp,
                        'alamat' => str($this->record->alamat)->title()->toString() . ' ' . $this->record->kabKota?->nama,
                        'telpon' => $this->record->telepon,
                        'nomor_register' => $this->record->no_register,
                        'tanggal_register' => $this->record->tanggal_verifikasi?->translatedFormat('d F Y'),
                        'berlaku_hingga' => $this->record->tanggal_berlaku?->translatedFormat('d F Y'),
                        'tanggal_ttd' => $this->record->tanggal_verifikasi?->translatedFormat('d F Y'),
                        'nama_kadis' => $biodataKadis->nama,
                        'pangkat_kadis' => $biodataKadis->jabatan,
                        'nip_kadis' => $biodataKadis->nip,
                        'kabupaten' => $this->record->kabKota?->nama,
                        'kel_desa' => $this->record->desa
                    ]);

                    $outputPath = storage_path('template/hasil-sp3.docx');
                    $template->saveAs($outputPath);

                    return response()->download($outputPath)->deleteFileAfterSend();
                }),
            Action::make('verify_kab_kota')
                ->label('Verifikasi Kab/Kota')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(function() {
                    $user = auth()->user();
                    return $user->wewenang->nama === 'Disnak Kab/Kota' &&
                           $user->kab_kota_id === $this->record->kab_kota_id &&
                           !$this->record->kab_kota_verified_at &&
                           $this->record->wewenang->nama === 'Pengguna';
                })
                ->form([
                    Textarea::make('catatan')
                        ->label('Catatan Verifikasi')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'kab_kota_verified_at' => now(),
                        'kab_kota_verified_by' => auth()->id(),
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
                    return $user->wewenang->nama === 'Disnak Provinsi' &&
                           $this->record->kab_kota_verified_at &&
                           !$this->record->provinsi_verified_at &&
                           $this->record->wewenang->nama === 'Pengguna';
                })
                ->form([
                    Textarea::make('catatan')
                        ->label('Catatan Verifikasi')
                        ->required(),
                    TextInput::make('no_sp3')
                        ->label('No. SP3')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('no_register')
                        ->label('Nomor Register')
                        ->required()
                        ->maxLength(255),
                ])
                ->action(function (array $data) {
                    $now = now();
                    $this->record->update([
                        'provinsi_verified_at' => $now,
                        'provinsi_verified_by' => auth()->id(),
                        'tanggal_verifikasi' => $now,
                        'tanggal_berlaku' => $now->copy()->addYears(3),
                        'no_sp3' => $data['no_sp3'],
                        'no_register' => $data['no_register']
                    ]);

                    Notification::make()
                        ->title('User berhasil diverifikasi oleh Provinsi')
                        ->success()
                        ->send();
                }),
        ];
    }
}