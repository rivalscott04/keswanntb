<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin user
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'wewenang_id' => 1,
            'bidang_id' => 1,
            'kab_kota_id' => 1,
            'no_hp' => '081234567890',
            'alamat' => 'Jl. Admin No. 1',
        ]);

        // Disnak Provinsi user
        User::create([
            'name' => 'Disnak Provinsi',
            'email' => 'disnakprovinsi@example.com',
            'password' => Hash::make('password'),
            'wewenang_id' => 2,
            'bidang_id' => 1,
            'no_hp' => '081234567891',
            'alamat' => 'Jl. Disnak Provinsi No. 1',
        ]);

        // Disnak Kab/Kota user
        User::create([
            'name' => 'Disnak Kab/Kota',
            'email' => 'disnakkabkota@example.com',
            'password' => Hash::make('password'),
            'wewenang_id' => 3,
            'bidang_id' => 1,
            'kab_kota_id' => 2,
            'no_hp' => '081234567892',
            'alamat' => 'Jl. Disnak Kab/Kota No. 1',
        ]);

        // DPMPTSP user
        User::create([
            'name' => 'DPMPTSP',
            'email' => 'dpmptsp@example.com',
            'password' => Hash::make('password'),
            'wewenang_id' => 4,
            'no_hp' => '081234567893',
            'alamat' => 'Jl. DPMPTSP No. 1',
        ]);

        // Regular user
        User::create([
            'name' => 'Pengusaha',
            'email' => 'pengusaha@example.com',
            'password' => Hash::make('password'),
            'wewenang_id' => 5,
            'kab_kota_id' => 3,
            'no_hp' => '081234567894',
            'alamat' => 'Jl. Pengusaha No. 1',
        ]);
    }
}
