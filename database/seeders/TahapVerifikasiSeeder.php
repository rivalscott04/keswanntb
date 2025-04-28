<?php

namespace Database\Seeders;

use App\Models\TahapVerifikasi;
use Illuminate\Database\Seeder;

class TahapVerifikasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TahapVerifikasi::create(['nama' => 'Pengusaha', 'urutan' => 1]);
        TahapVerifikasi::create(['nama' => 'Disnak Kab/Kota NTB Asal', 'urutan' => 2]);
        TahapVerifikasi::create(['nama' => 'Disnak Kab/Kota NTB Tujuan', 'urutan' => 3]);
        TahapVerifikasi::create(['nama' => 'Disnak Provinsi', 'urutan' => 4]);
        TahapVerifikasi::create(['nama' => 'DPMPTSP', 'urutan' => 5]);
    }
} 