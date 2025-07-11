<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PelabuhanService;

class RefreshPelabuhanCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pelabuhan:refresh-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh cache data pelabuhan dari API Kemenhub';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai refresh cache data pelabuhan...');
        
        try {
            // Clear existing cache
            PelabuhanService::clearCache();
            $this->info('Cache lama berhasil dihapus.');
            
            // Fetch new data
            $pelabuhanList = PelabuhanService::getPelabuhanList();
            
            if (empty($pelabuhanList)) {
                $this->error('Gagal mengambil data pelabuhan dari API.');
                return 1;
            }
            
            $this->info('Berhasil mengambil ' . count($pelabuhanList) . ' data pelabuhan.');
            $this->info('Cache data pelabuhan berhasil diperbarui.');
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan: ' . $e->getMessage());
            return 1;
        }
    }
} 