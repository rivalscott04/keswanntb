<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Services\PelabuhanService;

class PelabuhanStatusWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        $stats = PelabuhanService::getPelabuhanStats();
        $lastError = PelabuhanService::getLastError();
        $isLoading = PelabuhanService::isDataLoading();
        
        $statsArray = [
            Stat::make('Total Pelabuhan', $stats['total'])
                ->description($stats['is_from_api'] ? 'Data dari API Kemenhub 2024' : 'Data fallback')
                ->descriptionIcon('heroicon-m-ship')
                ->color($stats['is_from_api'] ? 'success' : 'warning'),
            
            Stat::make('Status Cache', $stats['is_from_api'] ? 'Aktif' : 'Fallback')
                ->description($stats['last_updated'] ? 'Terakhir diperbarui: ' . $stats['last_updated']->format('d/m/Y H:i') : 'Belum pernah diperbarui')
                ->descriptionIcon('heroicon-m-clock')
                ->color($stats['is_from_api'] ? 'success' : 'danger'),
        ];

        if ($isLoading) {
            $statsArray[] = Stat::make('Status', 'Loading...')
                ->description('Sedang memuat data dari API Kemenhub')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('info');
        }

        if ($lastError) {
            $statsArray[] = Stat::make('Error Terakhir', 'Ada')
                ->description(substr($lastError, 0, 50) . '...')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger');
        }

        // Add connection status
        $statsArray[] = Stat::make('Koneksi API', $stats['is_from_api'] ? 'Tersedia' : 'Tidak Tersedia')
            ->description($stats['is_from_api'] ? 'API Kemenhub berfungsi normal' : 'Menggunakan data fallback')
            ->descriptionIcon($stats['is_from_api'] ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle')
            ->color($stats['is_from_api'] ? 'success' : 'danger');

        return $statsArray;
    }

    public static function canView(): bool
    {
        return auth()->user()->is_admin;
    }
} 