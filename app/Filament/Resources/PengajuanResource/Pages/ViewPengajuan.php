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
                ->color('success')
                ->visible(fn($record) => $record->canVerifyBy($user))
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
                    ])->columns(),
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
                        TextEntry::make('jumlah_ternak')->label('Jumlah Ternak'),
                        TextEntry::make('tahapVerifikasi.nama')->label('Tahap Verifikasi Saat Ini'),
                    ])->columns(2),
                Section::make('Dokumen')
                    ->schema([
                        TextEntry::make('surat_permohonan')
                            ->label('Surat Permohonan Perusahaan')
                            ->formatStateUsing(fn($state) => $state ? '<a href="' . \Storage::disk('public')->url($state) . '" target="_blank" class="text-primary-600 dark:text-primary-400">Lihat Dokumen</a>' : '')
                            ->html()
                            ->visible(fn($state) => $state),
                        TextEntry::make('nomor_surat_permohonan')->label('Nomor Surat Permohonan'),
                        TextEntry::make('tanggal_surat_permohonan')->label('Tanggal Surat Permohonan')->date(),
                        TextEntry::make('skkh')
                            ->label('SKKH')
                            ->formatStateUsing(fn($state) => $state ? '<a href="' . \Storage::disk('public')->url($state) . '" target="_blank" class="text-primary-600 dark:text-primary-400">Lihat Dokumen</a>' : '')
                            ->html()
                            ->visible(fn($state) => $state),
                        TextEntry::make('nomor_skkh')->label('No. SKKH'),
                        TextEntry::make('hasil_uji_lab')
                            ->label('Hasil Uji Lab')
                            ->formatStateUsing(fn($state) => $state ? '<a href="' . \Storage::disk('public')->url($state) . '" target="_blank" class="text-primary-600 dark:text-primary-400">Lihat Dokumen</a>' : '')
                            ->html()
                            ->visible(fn($state) => $state),
                        TextEntry::make('dokumen_lainnya')
                            ->label('Dokumen Lainnya')
                            ->formatStateUsing(fn($state) => $state ? '<a href="' . \Storage::disk('public')->url($state) . '" target="_blank" class="text-primary-600 dark:text-primary-400">Lihat Dokumen</a>' : '')
                            ->html()
                            ->visible(fn($state) => $state),
                    ])->columns(2),
            ]);
    }
}
