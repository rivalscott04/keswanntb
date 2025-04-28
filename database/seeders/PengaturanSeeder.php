<?php

namespace Database\Seeders;

use App\Models\Pengaturan;
use Illuminate\Database\Seeder;

class PengaturanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Pengaturan::create([
            'key' => 'biodata_kadis',
            'value' => json_encode([
                'nama' => 'L. Ahmad Nur Aulia',
                'jabatan' => 'Pembina Tingkat I (IV/b)',
                'nip' => '19780703 199612 1 001',
            ]),
        ]);
    }
} 