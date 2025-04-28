<?php

namespace Database\Seeders;

use App\Models\JenisTernak;
use Illuminate\Database\Seeder;

class JenisTernakSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     */
    public function run(): void
    {
        JenisTernak::create(['kategori_ternak_id' => 1, 'nama' => 'Sapi', 'bidang_id' => 1]);
        JenisTernak::create(['kategori_ternak_id' => 1, 'nama' => 'Kerbau', 'bidang_id' => 1]);
        JenisTernak::create(['kategori_ternak_id' => 1, 'nama' => 'Kuda', 'bidang_id' => 1]);
        JenisTernak::create(['kategori_ternak_id' => 1, 'nama' => 'Sapi Eksotik', 'bidang_id' => 1]);

        JenisTernak::create(['kategori_ternak_id' => 2, 'nama' => 'Kambing', 'bidang_id' => 1]);
        JenisTernak::create(['kategori_ternak_id' => 2, 'nama' => 'Babi', 'bidang_id' => 1]);
        JenisTernak::create(['kategori_ternak_id' => 2, 'nama' => 'Domba', 'bidang_id' => 1]);

        JenisTernak::create(['kategori_ternak_id' => 3, 'nama' => 'Bibit Ayam', 'bidang_id' => 1]);
        JenisTernak::create(['kategori_ternak_id' => 3, 'nama' => 'Bibit Bebek/Itik', 'bidang_id' => 1]);
        JenisTernak::create(['kategori_ternak_id' => 3, 'nama' => 'Bibit Puyuh', 'bidang_id' => 1]);
        JenisTernak::create(['kategori_ternak_id' => 3, 'nama' => 'Telur Tetas', 'bidang_id' => 1]);
        JenisTernak::create(['kategori_ternak_id' => 3, 'nama' => 'Ayam Dara / Potong', 'bidang_id' => 1]);
        JenisTernak::create(['kategori_ternak_id' => 3, 'nama' => 'Bibit Sapi', 'bidang_id' => 1]);

        JenisTernak::create(['kategori_ternak_id' => 4, 'nama' => 'Daging Ayam Beku', 'bidang_id' => 2]);
        JenisTernak::create(['kategori_ternak_id' => 4, 'nama' => 'Daging Sapi Beku', 'bidang_id' => 2]);
        JenisTernak::create(['kategori_ternak_id' => 4, 'nama' => 'Daging Ayam Olahan', 'bidang_id' => 2]);
        JenisTernak::create(['kategori_ternak_id' => 4, 'nama' => 'Telur Konsumsi dan Susu', 'bidang_id' => 2]);

        JenisTernak::create(['kategori_ternak_id' => 5, 'nama' => 'Anjing', 'bidang_id' => 3]);
        JenisTernak::create(['kategori_ternak_id' => 5, 'nama' => 'Kuda', 'bidang_id' => 3]);
        JenisTernak::create(['kategori_ternak_id' => 5, 'nama' => 'Kucing', 'bidang_id' => 3]);
    }
} 