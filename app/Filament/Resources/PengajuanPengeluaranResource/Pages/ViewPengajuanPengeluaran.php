<?php

namespace App\Filament\Resources\PengajuanPengeluaranResource\Pages;

use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use App\Services\PengajuanService;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\PengajuanPengeluaranResource;

class ViewPengajuanPengeluaran extends ViewRecord
{
    protected static string $resource = PengajuanPengeluaranResource::class;

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
                ->color('success')
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
            Actions\EditAction::make()
                ->visible(fn($record) => ($user->id === $record->user_id && in_array($record->status, ['menunggu', 'ditolak'])) || $user->is_admin),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Pengajuan')
                    ->schema([
                        Infolists\Components\TextEntry::make('tahun_pengajuan')
                            ->label('Tahun Pengajuan'),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'menunggu' => 'gray',
                                'disetujui' => 'success',
                                'ditolak' => 'danger',
                                'diproses' => 'warning',
                                'selesai' => 'success',
                            }),
                        Infolists\Components\TextEntry::make('jumlah_ternak')
                            ->label('Jumlah Ternak yang Diajukan')
                            ->badge()
                            ->color('info'),
                    ])->columns(2),

                Infolists\Components\Section::make('Informasi Kuota')
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('Sisa Kuota Pengeluaran')
                            ->badge()
                            ->color(function($record) {
                                $kuotaSisa = $this->getKuotaPengeluaranAsal($record);
                                return $kuotaSisa <= 0 ? 'danger' : ($kuotaSisa < $record->jumlah_ternak ? 'warning' : 'success');
                            })
                            ->formatStateUsing(function($record) {
                                return $this->getKuotaPengeluaranAsal($record) . ' ekor';
                            }),
                        Infolists\Components\TextEntry::make('id')
                            ->label('Status Kuota')
                            ->badge()
                            ->color(function($record) {
                                $kuotaSisa = $this->getKuotaPengeluaranAsal($record);
                                $jumlahDiajukan = $record->jumlah_ternak;
                                return $kuotaSisa < $jumlahDiajukan ? 'danger' : 'success';
                            })
                            ->formatStateUsing(function($record) {
                                $kuotaSisa = $this->getKuotaPengeluaranAsal($record);
                                $jumlahDiajukan = $record->jumlah_ternak;
                                if ($kuotaSisa < $jumlahDiajukan) {
                                    return 'Kuota Tidak Cukup';
                                }
                                return 'Kuota Tersedia';
                            }),
                    ])->columns(),

                Infolists\Components\Section::make('Lokasi')
                    ->schema([
                        Infolists\Components\TextEntry::make('kabKotaAsal.nama')
                            ->label('Kab/Kota Asal'),
                        Infolists\Components\TextEntry::make('pelabuhan_asal')
                            ->label('Pelabuhan Asal'),
                        Infolists\Components\TextEntry::make('provinsiTujuan.nama')
                            ->label('Provinsi Tujuan'),
                        Infolists\Components\TextEntry::make('kab_kota_tujuan')
                            ->label('Kota Tujuan'),
                        Infolists\Components\TextEntry::make('pelabuhan_tujuan')
                            ->label('Pelabuhan Tujuan'),
                    ])->columns(2),

                Infolists\Components\Section::make('Informasi Ternak')
                    ->schema([
                        Infolists\Components\TextEntry::make('kategoriTernak.nama')
                            ->label('Kategori Ternak'),
                        Infolists\Components\TextEntry::make('jenisTernak.nama')
                            ->label('Jenis Ternak'),
                        Infolists\Components\TextEntry::make('jenis_kelamin')
                            ->label('Jenis Kelamin'),
                        Infolists\Components\TextEntry::make('ras_ternak')
                            ->label('Ras Ternak'),
                    ])->columns(2),

                Infolists\Components\Section::make('Dokumen')
                    ->schema([
                        Infolists\Components\TextEntry::make('nomor_surat_permohonan')
                            ->label('Nomor Surat Permohonan'),
                        Infolists\Components\TextEntry::make('tanggal_surat_permohonan')
                            ->label('Tanggal Surat Permohonan')
                            ->date(),
                        Infolists\Components\TextEntry::make('nomor_skkh')
                            ->label('Nomor SKKH'),
                        Infolists\Components\TextEntry::make('surat_permohonan')
                            ->label('Surat Permohonan')
                            ->html()
                            ->formatStateUsing(fn($state) => $state ? '<a href="' . asset('storage/' . $state) . '" target="_blank" class="text-primary-600 dark:text-primary-400">Lihat Dokumen</a>' : '')
                            ->visible(fn($state) => $state),
                        Infolists\Components\TextEntry::make('skkh')
                            ->label('SKKH')
                            ->html()
                            ->formatStateUsing(fn($state) => $state ? '<a href="' . asset('storage/' . $state) . '" target="_blank" class="text-primary-600 dark:text-primary-400">Lihat Dokumen</a>' : '')
                            ->visible(fn($state) => $state),
                        Infolists\Components\TextEntry::make('hasil_uji_lab')
                            ->label('Hasil Uji Lab')
                            ->html()
                            ->formatStateUsing(fn($state) => $state ? '<a href="' . asset('storage/' . $state) . '" target="_blank" class="text-primary-600 dark:text-primary-400">Lihat Dokumen</a>' : '')
                            ->visible(fn($state) => $state),
                        Infolists\Components\TextEntry::make('dokumen_lainnya')
                            ->label('Dokumen Lainnya')
                            ->html()
                            ->formatStateUsing(fn($state) => $state ? '<a href="' . asset('storage/' . $state) . '" target="_blank" class="text-primary-600 dark:text-primary-400">Lihat Dokumen</a>' : '')
                            ->visible(fn($state) => $state),
                        Infolists\Components\TextEntry::make('izin_ptsp_daerah')
                            ->label('Izin PTSP Daerah')
                            ->html()
                            ->formatStateUsing(fn($state) => $state ? '<a href="' . asset('storage/' . $state) . '" target="_blank" class="text-primary-600 dark:text-primary-400">Lihat Dokumen</a>' : '')
                            ->visible(fn($state) => $state),
                    ])->columns(2),
            ]);
    }

    /**
     * Get sisa kuota pengeluaran dari asal
     */
    private function getKuotaPengeluaranAsal($record): int
    {
        // Daftar kab/kota di pulau Lombok
        $kabKotaLombok = [
            'Kota Mataram',
            'Kab. Lombok Barat', 
            'Kab. Lombok Tengah',
            'Kab. Lombok Timur',
            'Kab. Lombok Utara'
        ];

        $kabKotaAsal = $record->kabKotaAsal;
        $isLombokAsal = $kabKotaAsal && in_array($kabKotaAsal->nama, $kabKotaLombok);

        if ($isLombokAsal) {
            // Pengeluaran dari Lombok: global
            return \App\Models\PenggunaanKuota::getKuotaTersisaLombok(
                $record->tahun_pengajuan,
                $record->jenis_ternak_id,
                $record->jenis_kelamin,
                'pengeluaran'
            );
        } else {
            // Pengeluaran dari kab/kota lain: per kab/kota
            return \App\Models\PenggunaanKuota::getKuotaTersisa(
                $record->tahun_pengajuan,
                $record->jenis_ternak_id,
                $record->kab_kota_asal_id,
                $record->jenis_kelamin,
                'pengeluaran'
            );
        }
    }
}