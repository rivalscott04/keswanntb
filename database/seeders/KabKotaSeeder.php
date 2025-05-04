<?php

namespace Database\Seeders;

use App\Models\KabKota;
use App\Models\Provinsi;
use Illuminate\Database\Seeder;

class KabKotaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     */
    public function run(): void
    {
        $ntb = Provinsi::where('nama', 'Nusa Tenggara Barat')->first();

        KabKota::create(['nama' => 'Kota Mataram', 'provinsi_id' => $ntb->id]);
        KabKota::create(['nama' => 'Kab. Lombok Barat', 'provinsi_id' => $ntb->id]);
        KabKota::create(['nama' => 'Kab. Lombok Tengah', 'provinsi_id' => $ntb->id]);
        KabKota::create(['nama' => 'Kab. Lombok Timur', 'provinsi_id' => $ntb->id]);
        KabKota::create(['nama' => 'Kab. Lombok Utara', 'provinsi_id' => $ntb->id]);

        KabKota::create(['nama' => 'Kab. Sumbawa Barat', 'provinsi_id' => $ntb->id]);
        KabKota::create(['nama' => 'Kab. Sumbawa', 'provinsi_id' => $ntb->id]);
        KabKota::create(['nama' => 'Kab. Dompu', 'provinsi_id' => $ntb->id]);
        KabKota::create(['nama' => 'Kab. Bima', 'provinsi_id' => $ntb->id]);
        KabKota::create(['nama' => 'Kota Bima', 'provinsi_id' => $ntb->id]);
    }
}