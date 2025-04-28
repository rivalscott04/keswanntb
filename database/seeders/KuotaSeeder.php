<?php

namespace Database\Seeders;

use App\Models\Kuota;
use Illuminate\Database\Seeder;

class KuotaSeeder extends Seeder
{
    public function run(): void
    {
        // Kuota terima sapi
        Kuota::create([
            'tahun' => 2023,
            'jenis_ternak_id' => 1,
            'kab_kota_id' => 1,
            'kuota' => 7500,
        ]);

        Kuota::create([
            'tahun' => 2023,
            'jenis_ternak_id' => 1,
            'kab_kota_id' => 2,
            'kuota' => 21000,
        ]);

        Kuota::create([
            'tahun' => 2023,
            'jenis_ternak_id' => 1,
            'kab_kota_id' => 3,
            'kuota' => 5000,
        ]);

        Kuota::create([
            'tahun' => 2023,
            'jenis_ternak_id' => 1,
            'kab_kota_id' => 4,
            'kuota' => 5000,
        ]);

        // Kuota terima kerbau
        Kuota::create([
            'tahun' => 2023,
            'jenis_ternak_id' => 2,
            'kab_kota_id' => 6,
            'kuota' => 500,
        ]);

        Kuota::create([
            'tahun' => 2023,
            'jenis_ternak_id' => 2,
            'kab_kota_id' => 7,
            'kuota' => 1500,
        ]);

        Kuota::create([
            'tahun' => 2023,
            'jenis_ternak_id' => 2,
            'kab_kota_id' => 8,
            'kuota' => 550,
        ]);

        Kuota::create([
            'tahun' => 2023,
            'jenis_ternak_id' => 2,
            'kab_kota_id' => 9,
            'kuota' => 900,
        ]);

        // Kuota terima sapi eksotik
        Kuota::create([
            'tahun' => 2023,
            'jenis_ternak_id' => 4,
            'kab_kota_id' => 1,
            'kuota' => 60000,
        ]);
    }
} 