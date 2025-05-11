<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\KabKota;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

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
            'no_hp' => '081234567890',
            'alamat' => 'Jl. Airlangga No. 56',
            'is_admin' => true,
        ]);

        // Disnak Provinsi user
        User::create([
            'name' => 'Disnak Provinsi',
            'email' => 'disnakprovinsi@example.com',
            'password' => Hash::make('password'),
            'wewenang_id' => 2,
            'bidang_id' => 1,
            'no_hp' => '081234567891',
            'alamat' => 'Jl. Airlangga No. 56',
        ]);

        $kabKotas = KabKota::take(10)->get();

        foreach ($kabKotas as $kabKota) {
            // Buat verifikator untuk kabupaten/kota
            User::create([
                'name' => $kabKota->nama,
                'email' => strtolower(str_replace([' ', '.', 'Kab', 'Kota'], '', $kabKota->nama) . (str_contains($kabKota->nama, 'Kab') ? 'kab' : 'kota')) . '@example.com',
                'password' => Hash::make('password'),
                'wewenang_id' => 3,
                'kab_kota_id' => $kabKota->id,
                'is_admin' => false,
                'is_pernah_daftar' => true,
                'status' => 1,
                'jenis_akun' => 'disnak',
                'alamat' => $kabKota->nama,
            ]);
        }

        // DPMPTSP user
        User::create([
            'name' => 'DPMPTSP',
            'email' => 'dpmptsp@example.com',
            'password' => Hash::make('password'),
            'wewenang_id' => 4,
            'no_hp' => '081234567893',
            'alamat' => 'Jl. Udayana No. 4',
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
            'account_verified_at' => now(),
            'account_verified_by' => 1,
        ]);
    }
}
