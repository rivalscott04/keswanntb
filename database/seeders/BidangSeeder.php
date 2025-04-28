<?php

namespace Database\Seeders;

use App\Models\Bidang;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BidangSeeder extends Seeder
{
    public function run(): void
    {
        Bidang::create(['nama' => 'Bidang Penyuluhan Pengolahan dan Pemasaran Hasil Peternakan']);
        Bidang::create(['nama' => 'Bidang Kesehatan Masyarakat Veteriner']);
        Bidang::create(['nama' => 'Bidang Kesehatan Hewan']);
    }
} 