<?php

namespace Database\Seeders;

use App\Models\Provinsi;
use Illuminate\Database\Seeder;

class ProvinsiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 34 Provinsi di Indonesia (update 2024)
        $provinsi = [
            // Sumatera (10 provinsi)
            'Aceh',
            'Sumatera Utara',
            'Sumatera Barat',
            'Riau',
            'Kepulauan Riau',
            'Jambi',
            'Sumatera Selatan',
            'Bangka Belitung',
            'Bengkulu',
            'Lampung',
            
            // Jawa (6 provinsi)
            'DKI Jakarta',
            'Jawa Barat',
            'Jawa Tengah',
            'Yogyakarta',
            'Jawa Timur',
            'Banten',
            
            // Bali & Nusa Tenggara (3 provinsi)
            'Bali',
            'Nusa Tenggara Barat',
            'Nusa Tenggara Timur',
            
            // Kalimantan (5 provinsi)
            'Kalimantan Barat',
            'Kalimantan Tengah',
            'Kalimantan Selatan',
            'Kalimantan Timur',
            'Kalimantan Utara',
            
            // Sulawesi (6 provinsi)
            'Sulawesi Utara',
            'Sulawesi Tengah',
            'Sulawesi Selatan',
            'Sulawesi Tenggara',
            'Gorontalo',
            'Sulawesi Barat',
            
            // Maluku (2 provinsi)
            'Maluku',
            'Maluku Utara',
            
            // Papua (2 provinsi)
            'Papua Barat',
            'Papua',
        ];

        foreach ($provinsi as $nama) {
            Provinsi::updateOrCreate(
                ['nama' => $nama],
                ['nama' => $nama]
            );
        }
    }
} 