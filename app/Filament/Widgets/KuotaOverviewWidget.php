<?php

namespace App\Filament\Widgets;

use App\Models\Kuota;
use App\Models\PenggunaanKuota;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Forms\Components\Select;

class KuotaOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    public ?int $tahun = null;

    protected static ?string $pollingInterval = '30s';

    public function mount(): void
    {
        $this->tahun = now()->year;
    }

    protected function getStats(): array
    {
        // Gunakan tahun yang dipilih atau default ke tahun berjalan
        $tahun = $this->tahun ?? now()->year;

        // Hitung kuota pemasukan
        $totalKuotaPemasukan = Kuota::where('tahun', $tahun)
            ->where('jenis_kuota', 'pemasukan')
            ->sum('kuota');
        
        $terpakaiPemasukan = PenggunaanKuota::where('tahun', $tahun)
            ->where('jenis_penggunaan', 'pemasukan')
            ->sum('jumlah_digunakan');
        
        $sisaPemasukan = max(0, $totalKuotaPemasukan - $terpakaiPemasukan);
        $persentasePemasukan = $totalKuotaPemasukan > 0 
            ? round(($terpakaiPemasukan / $totalKuotaPemasukan) * 100, 2) 
            : 0;

        // Hitung kuota pengeluaran
        $totalKuotaPengeluaran = Kuota::where('tahun', $tahun)
            ->where('jenis_kuota', 'pengeluaran')
            ->sum('kuota');
        
        $terpakaiPengeluaran = PenggunaanKuota::where('tahun', $tahun)
            ->where('jenis_penggunaan', 'pengeluaran')
            ->sum('jumlah_digunakan');
        
        $sisaPengeluaran = max(0, $totalKuotaPengeluaran - $terpakaiPengeluaran);
        $persentasePengeluaran = $totalKuotaPengeluaran > 0 
            ? round(($terpakaiPengeluaran / $totalKuotaPengeluaran) * 100, 2) 
            : 0;

        return [
            Stat::make('Total Kuota Pemasukan', number_format($totalKuotaPemasukan, 0, ',', '.'))
                ->description('Sisa: ' . number_format($sisaPemasukan, 0, ',', '.') . ' (' . (100 - $persentasePemasukan) . '%)')
                ->descriptionIcon('heroicon-m-arrow-down-circle')
                ->color('success'),
            
            Stat::make('Total Kuota Pengeluaran', number_format($totalKuotaPengeluaran, 0, ',', '.'))
                ->description('Sisa: ' . number_format($sisaPengeluaran, 0, ',', '.') . ' (' . (100 - $persentasePengeluaran) . '%)')
                ->descriptionIcon('heroicon-m-arrow-up-circle')
                ->color('warning'),
            
            Stat::make('Kuota Terpakai (Pemasukan)', number_format($terpakaiPemasukan, 0, ',', '.'))
                ->description($persentasePemasukan . '% dari total pemasukan')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),
            
            Stat::make('Kuota Terpakai (Pengeluaran)', number_format($terpakaiPengeluaran, 0, ',', '.'))
                ->description($persentasePengeluaran . '% dari total pengeluaran')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('danger'),
        ];
    }

    protected function getFilters(): ?array
    {
        // Ambil daftar tahun yang tersedia dari tabel kuota
        $tahunTersedia = Kuota::select('tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();

        // Jika tidak ada tahun di database, gunakan tahun berjalan dan beberapa tahun sebelumnya
        if (empty($tahunTersedia)) {
            $currentYear = now()->year;
            $tahunTersedia = range($currentYear, $currentYear - 4);
        }

        // Buat array options untuk select
        $options = array_combine($tahunTersedia, $tahunTersedia);

        return [
            Select::make('tahun')
                ->label('Tahun')
                ->options($options)
                ->default(now()->year)
                ->searchable()
                ->reactive()
                ->afterStateUpdated(function ($state) {
                    $this->tahun = $state;
                    $this->dispatch('updateStats');
                }),
        ];
    }
}

