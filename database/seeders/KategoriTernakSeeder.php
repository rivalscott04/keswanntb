<?php

namespace Database\Seeders;

use App\Models\KategoriTernak;
use Illuminate\Database\Seeder;

class KategoriTernakSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     */
    public function run(): void
    {
        KategoriTernak::create(['nama' => 'Ruminansia Besar']);
        KategoriTernak::create(['nama' => 'Ruminansia Kecil']);
        KategoriTernak::create(['nama' => 'Unggas & Telur']);
        KategoriTernak::create(['nama' => 'Produk Ternak']);
        KategoriTernak::create(['nama' => 'Hewan Kesayangan']);
    }
} 