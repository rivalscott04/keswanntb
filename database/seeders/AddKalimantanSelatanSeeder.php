<?php

namespace Database\Seeders;

use App\Models\Provinsi;
use Illuminate\Database\Seeder;

class AddKalimantanSelatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeder ini menambahkan provinsi yang hilang
     * ke database yang sudah ada (Kalimantan Selatan dan Maluku Utara).
     *
     * @return void
     */
    public function run()
    {
        $provinsiHilang = [
            'Kalimantan Selatan',
            'Maluku Utara'
        ];

        foreach ($provinsiHilang as $namaProvinsi) {
            $provinsi = Provinsi::where('nama', $namaProvinsi)->first();
            
            if (!$provinsi) {
                Provinsi::create(['nama' => $namaProvinsi]);
                $this->command->info("Provinsi {$namaProvinsi} berhasil ditambahkan.");
            } else {
                $this->command->info("Provinsi {$namaProvinsi} sudah ada di database.");
            }
        }
    }
}
