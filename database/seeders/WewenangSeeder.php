<?php

namespace Database\Seeders;

use App\Models\Wewenang;
use Illuminate\Database\Seeder;

class WewenangSeeder extends Seeder
{
    public function run(): void
    {
        Wewenang::create(['nama' => 'admin']);
        Wewenang::create(['nama' => 'Disnak Provinsi']);
        Wewenang::create(['nama' => 'Disnak Kab/Kota']);
        Wewenang::create(['nama' => 'DPMPTSP']);
        Wewenang::create(['nama' => 'biasa']);
    }
} 