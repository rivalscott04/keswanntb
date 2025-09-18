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
                
                // Return fallback data instead of empty array
                return self::getFallbackPelabuhanData();
            }
        });
    }

    public static function getFallbackPelabuhanData()
    {
        try {
            // Try to load from the local API response file
            $jsonPath = base_path('api_response.json');
            
            if (file_exists($jsonPath)) {
                $jsonContent = file_get_contents($jsonPath);
                $data = json_decode($jsonContent, true);
                
                if (is_array($data) && !empty($data)) {
                    $pelabuhanList = [];
                    
                    foreach ($data as $item) {
                        if (isset($item['uraian']) && !empty($item['uraian'])) {
                            $pelabuhanList[$item['uraian']] = $item['uraian'];
                        }
                    }
                    
                    // Sort alphabetically
                    asort($pelabuhanList);
                    
                    // Add "Lainnya" option
                    $pelabuhanList['Lainnya'] = 'Lainnya';
                    
                    return $pelabuhanList;
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to load fallback data from api_response.json: ' . $e->getMessage());
        }
        
        // If JSON file fails, return basic fallback data
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

    public static function getPelabuhanListWithFallback()
    {
        $pelabuhanList = self::getPelabuhanList();
        
        // If API fails, return fallback data
        if (empty($pelabuhanList)) {
            return self::getFallbackPelabuhanData();
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
        
        // Always return the fallback options, even if there's an error
        // The error will be shown in helper text instead
        return self::getPelabuhanOptions();
    }

    public static function getPelabuhanPlaceholder()
    {
        if (self::isDataLoading()) {
            return 'Memuat data pelabuhan...';
        }
        
        $stats = self::getPelabuhanStats();
        if ($stats['is_from_api']) {
            return 'Pilih pelabuhan (' . $stats['total'] . ' data tersedia)';
        }
        
        return 'Pilih pelabuhan';
    }

    public static function getPelabuhanHelperText()
    {
        if (self::isDataLoading()) {
            return 'Sedang memuat data pelabuhan...';
        }
        
        $lastError = self::getLastError();
        if ($lastError) {
            // Check if we're using the comprehensive fallback data
            $fallbackData = self::getFallbackPelabuhanData();
            $isUsingJsonFallback = count($fallbackData) > 20; // JSON has many more ports than basic fallback
            
            if ($isUsingJsonFallback) {
                return 'Menggunakan data pelabuhan lengkap dari backup. Jika pelabuhan yang Anda cari tidak ada, pilih "Lainnya" dan isi manual.';
            } else {
                return 'Menggunakan data pelabuhan standar. Jika pelabuhan yang Anda cari tidak ada, pilih "Lainnya" dan isi manual.';
            }
        }
        
        $stats = self::getPelabuhanStats();
        if ($stats['is_from_api']) {
            return 'Data pelabuhan terbaru (' . $stats['total'] . ' data tersedia)';
        }
        
        return 'Menggunakan data pelabuhan standar. Jika pelabuhan yang Anda cari tidak ada, pilih "Lainnya" dan isi manual.';
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

    public static function retryApiConnection()
    {
        try {
            self::clearCache();
            self::clearError();
            
            // Test connection with a shorter timeout
            $response = Http::timeout(10)->get('https://portaldata.kemenhub.go.id/api/microstrategy/data_stathub_uk', [
                'id_tabel' => 'A.1.5.09',
                'tahun' => '2024',
                'format' => 'json'
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Koneksi ke API Kemenhub berhasil'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'API Kemenhub tidak merespons (Status: ' . $response->status() . ')'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Tidak dapat terhubung ke API Kemenhub: ' . $e->getMessage()
            ];
        }
    }

    public static function getFallbackDataStatus()
    {
        $jsonPath = base_path('api_response.json');
        
        if (!file_exists($jsonPath)) {
            return [
                'available' => false,
                'message' => 'File api_response.json tidak ditemukan'
            ];
        }
        
        try {
            $jsonContent = file_get_contents($jsonPath);
            $data = json_decode($jsonContent, true);
            
            if (is_array($data) && !empty($data)) {
                $portCount = count($data);
                return [
                    'available' => true,
                    'message' => "File backup tersedia dengan {$portCount} data pelabuhan",
                    'count' => $portCount
                ];
            } else {
                return [
                    'available' => false,
                    'message' => 'File api_response.json tidak valid atau kosong'
                ];
            }
        } catch (\Exception $e) {
            return [
                'available' => false,
                'message' => 'Error membaca file api_response.json: ' . $e->getMessage()
            ];
        }
    }
} 