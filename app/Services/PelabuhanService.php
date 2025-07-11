<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class PelabuhanService
{
    public static function getPelabuhanList()
    {
        // Cache data pelabuhan selama 24 jam
        return Cache::remember('pelabuhan_list', 60 * 24, function () {
            try {
                self::setLoadingState(true);
                self::clearError();
                
                $response = Http::timeout(30)->get('https://portaldata.kemenhub.go.id/api/microstrategy/data_stathub_uk', [
                    'id_tabel' => 'A.1.5.09',
                    'tahun' => '2024',
                    'format' => 'json'
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    // Extract pelabuhan data from response
                    $pelabuhanList = [];
                    
                    // API Kemenhub mengembalikan array langsung, bukan dalam key 'data'
                    if (is_array($data)) {
                        foreach ($data as $item) {
                            if (isset($item['uraian']) && !empty($item['uraian'])) {
                                $pelabuhanList[$item['uraian']] = $item['uraian'];
                            }
                        }
                    }
                    
                    // Sort alphabetically
                    asort($pelabuhanList);
                    
                    // Update timestamp and clear any previous errors
                    self::updateLastUpdatedTimestamp();
                    self::clearError();
                    
                    self::setLoadingState(false);
                    return $pelabuhanList;
                }
                
                // If response is not successful, throw exception
                throw new \Exception('API response tidak berhasil: ' . $response->status());
                
            } catch (\Exception $e) {
                // Log error and set error state
                \Log::error('Error fetching pelabuhan data: ' . $e->getMessage());
                self::setLastError($e->getMessage());
                self::setLoadingState(false);
                
                // Return empty array if API fails
                return [];
            }
        });
    }

    public static function getPelabuhanListWithFallback()
    {
        $pelabuhanList = self::getPelabuhanList();
        
        // If API fails, return some common Indonesian ports
        if (empty($pelabuhanList)) {
            return [
                'Pelabuhan Tanjung Priok' => 'Pelabuhan Tanjung Priok',
                'Pelabuhan Tanjung Perak' => 'Pelabuhan Tanjung Perak',
                'Pelabuhan Belawan' => 'Pelabuhan Belawan',
                'Pelabuhan Makassar' => 'Pelabuhan Makassar',
                'Pelabuhan Bitung' => 'Pelabuhan Bitung',
                'Pelabuhan Ambon' => 'Pelabuhan Ambon',
                'Pelabuhan Sorong' => 'Pelabuhan Sorong',
                'Pelabuhan Jayapura' => 'Pelabuhan Jayapura',
                'Pelabuhan Merauke' => 'Pelabuhan Merauke',
                'Pelabuhan Lembar' => 'Pelabuhan Lembar',
                'Pelabuhan Badas' => 'Pelabuhan Badas',
                'Pelabuhan Kayangan' => 'Pelabuhan Kayangan',
                'Pelabuhan Poto Tano' => 'Pelabuhan Poto Tano',
                'Pelabuhan Sape' => 'Pelabuhan Sape',
                'Lainnya' => 'Lainnya',
            ];
        }
        
        return $pelabuhanList;
    }

    public static function getPelabuhanOptions()
    {
        $pelabuhanList = self::getPelabuhanListWithFallback();
        
        // Add "Lainnya" option at the end if not already present
        if (!isset($pelabuhanList['Lainnya'])) {
            $pelabuhanList['Lainnya'] = 'Lainnya';
        }
        
        return $pelabuhanList;
    }

    public static function getPelabuhanByKabKota($kabKotaId = null)
    {
        // For now, return all pelabuhan options
        // In the future, this can be filtered by kab/kota if the API provides that data
        return self::getPelabuhanOptions();
    }

    public static function getAllPelabuhanOptions()
    {
        return self::getPelabuhanOptions();
    }

    public static function getPelabuhanAsal()
    {
        // For pemasukan, pelabuhan asal is from outside NTB
        return self::getPelabuhanOptions();
    }

    public static function getPelabuhanTujuan()
    {
        // For pemasukan, pelabuhan tujuan is in NTB
        return self::getPelabuhanOptions();
    }

    public static function clearCache()
    {
        Cache::forget('pelabuhan_list');
        Cache::forget('pelabuhan_list_updated_at');
    }

    public static function getPelabuhanStats()
    {
        $pelabuhanList = self::getPelabuhanList();
        $isLoading = self::isDataLoading();
        $lastError = self::getLastError();
        
        return [
            'total' => count($pelabuhanList),
            'last_updated' => Cache::get('pelabuhan_list_updated_at'),
            'is_from_api' => !empty($pelabuhanList) && !$lastError,
            'is_loading' => $isLoading,
            'has_error' => !empty($lastError),
            'error_message' => $lastError,
        ];
    }

    public static function updateLastUpdatedTimestamp()
    {
        Cache::put('pelabuhan_list_updated_at', now(), 60 * 24);
    }

    public static function isDataLoading()
    {
        return Cache::has('pelabuhan_list_loading');
    }

    public static function setLoadingState($loading = true)
    {
        if ($loading) {
            Cache::put('pelabuhan_list_loading', true, 60); // 1 minute timeout
        } else {
            Cache::forget('pelabuhan_list_loading');
        }
    }

    public static function getLastError()
    {
        return Cache::get('pelabuhan_list_error');
    }

    public static function setLastError($error)
    {
        Cache::put('pelabuhan_list_error', $error, 60 * 24);
    }

    public static function clearError()
    {
        Cache::forget('pelabuhan_list_error');
    }

    public static function getPelabuhanOptionsWithLoading()
    {
        if (self::isDataLoading()) {
            return [
                'loading' => 'Memuat data pelabuhan dari API Kemenhub...',
            ];
        }
        
        $lastError = self::getLastError();
        if ($lastError) {
            return [
                'error' => 'Error: ' . substr($lastError, 0, 50) . '...',
            ];
        }
        
        return self::getPelabuhanOptions();
    }

    public static function getPelabuhanPlaceholder()
    {
        if (self::isDataLoading()) {
            return 'Memuat data pelabuhan dari API Kemenhub...';
        }
        
        $lastError = self::getLastError();
        if ($lastError) {
            return 'Error loading data - menggunakan data fallback';
        }
        
        $stats = self::getPelabuhanStats();
        if ($stats['is_from_api']) {
            return 'Pilih pelabuhan (' . $stats['total'] . ' data tersedia dari API Kemenhub)';
        }
        
        return 'Pilih pelabuhan (menggunakan data fallback)';
    }

    public static function getPelabuhanHelperText()
    {
        if (self::isDataLoading()) {
            return 'Sedang memuat data pelabuhan dari API Kemenhub...';
        }
        
        $lastError = self::getLastError();
        if ($lastError) {
            return 'Gagal memuat data dari API Kemenhub. Menggunakan data fallback. Error: ' . substr($lastError, 0, 100) . '...';
        }
        
        $stats = self::getPelabuhanStats();
        if ($stats['is_from_api']) {
            return 'Data pelabuhan diambil dari API Kemenhub 2024 (' . $stats['total'] . ' data tersedia)';
        }
        
        return 'Menggunakan data pelabuhan fallback karena API Kemenhub tidak tersedia';
    }

    public static function refreshCache()
    {
        try {
            self::clearCache();
            self::clearError();
            self::setLoadingState(true);
            
            $pelabuhanList = self::getPelabuhanList();
            $stats = self::getPelabuhanStats();
            
            return [
                'success' => true,
                'total' => $stats['total'],
                'last_updated' => $stats['last_updated'],
                'message' => 'Berhasil memperbarui ' . $stats['total'] . ' data pelabuhan dari API Kemenhub'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Gagal memperbarui cache data pelabuhan'
            ];
        }
    }
} 