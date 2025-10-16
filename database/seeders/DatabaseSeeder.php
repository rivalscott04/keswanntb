<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            BidangSeeder::class,
            WewenangSeeder::class,
            KategoriTernakSeeder::class,
            ProvinsiSeeder::class,
            KabKotaSeeder::class,
            TahapVerifikasiSeeder::class,
            UserSeeder::class,
            JenisTernakSeeder::class,
            KuotaSeeder::class,
            PengaturanSeeder::class,
            ExistingUserSeeder::class,
            ContohDataSeeder::class,
            DokumenDummySeeder::class,
        ]);
    }
}
