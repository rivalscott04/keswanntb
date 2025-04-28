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
        Provinsi::create(['nama' => 'Aceh']);
        Provinsi::create(['nama' => 'Sumatera Utara']);
        Provinsi::create(['nama' => 'Sumatera Selatan']);
        Provinsi::create(['nama' => 'Sumatera Barat']);
        Provinsi::create(['nama' => 'Riau']);
        Provinsi::create(['nama' => 'Kepulauan Riau']);
        Provinsi::create(['nama' => 'Bangka Belitung']);
        Provinsi::create(['nama' => 'Bengkulu']);
        Provinsi::create(['nama' => 'Jambi']);
        Provinsi::create(['nama' => 'Lampung']);

        Provinsi::create(['nama' => 'Kalimantan Timur']);
        Provinsi::create(['nama' => 'Kalimantan Barat']);
        Provinsi::create(['nama' => 'Kalimantan Tengah']);
        Provinsi::create(['nama' => 'Kalimantan Utara']);
        Provinsi::create(['nama' => 'DKI Jakarta']);
        Provinsi::create(['nama' => 'Banten']);
        Provinsi::create(['nama' => 'Jawa Barat']);
        Provinsi::create(['nama' => 'Jawa Tengah']);
        Provinsi::create(['nama' => 'Yogyakarta']);
        Provinsi::create(['nama' => 'Jawa Timur']);

        Provinsi::create(['nama' => 'Bali']);
        Provinsi::create(['nama' => 'Nusa Tenggara Barat']);
        Provinsi::create(['nama' => 'Nusa Tenggara Timur']);
        Provinsi::create(['nama' => 'Sulawesi Utara']);
        Provinsi::create(['nama' => 'Sulawesi Barat']);
        Provinsi::create(['nama' => 'Sulawesi Tengah']);
        Provinsi::create(['nama' => 'Gorontalo']);
        Provinsi::create(['nama' => 'Sulawesi Tenggara']);
        Provinsi::create(['nama' => 'Sulawesi Selatan']);
        Provinsi::create(['nama' => 'Maluku']);

        Provinsi::create(['nama' => 'Papua Barat']);
        Provinsi::create(['nama' => 'Papua']);
    }
} 