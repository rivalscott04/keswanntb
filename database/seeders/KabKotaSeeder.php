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

        KabKota::updateOrCreate(
            ['nama' => 'Kota Mataram'],
            [
                'provinsi_id' => $ntb->id,
                'nama_dinas' => 'DINAS PERTANIAN',
                'alamat_dinas' => 'MATARAM'
            ]
        );
        KabKota::updateOrCreate(
            ['nama' => 'Kab. Lombok Barat'],
            [
                'provinsi_id' => $ntb->id,
                'nama_dinas' => 'DINAS PERTANIAN',
                'alamat_dinas' => 'LABUAPI'
            ]
        );
        KabKota::updateOrCreate(
            ['nama' => 'Kab. Lombok Tengah'],
            [
                'provinsi_id' => $ntb->id,
                'nama_dinas' => 'DINAS PERTANIAN',
                'alamat_dinas' => 'PRAYA'
            ]
        );
        KabKota::updateOrCreate(
            ['nama' => 'Kab. Lombok Timur'],
            [
                'provinsi_id' => $ntb->id,
                'nama_dinas' => 'DINAS PETERNAKAN DAN KESEHATAN HEWAN',
                'alamat_dinas' => 'SELONG'
            ]
        );
        KabKota::updateOrCreate(
            ['nama' => 'Kab. Lombok Utara'],
            [
                'provinsi_id' => $ntb->id,
                'nama_dinas' => 'DINAS KETAHANAN PANGAN PERTANIAN DAN PERIKANAN',
                'alamat_dinas' => 'GONDANG'
            ]
        );

        KabKota::updateOrCreate(
            ['nama' => 'Kab. Sumbawa Barat'],
            [
                'provinsi_id' => $ntb->id,
                'nama_dinas' => 'DINAS PERTANIAN',
                'alamat_dinas' => 'TALIWANG'
            ]
        );
        KabKota::updateOrCreate(
            ['nama' => 'Kab. Sumbawa'],
            [
                'provinsi_id' => $ntb->id,
                'nama_dinas' => 'DINAS PETERNAKAN DAN KESEHATAN HEWAN',
                'alamat_dinas' => 'SUMBAWA BESAR'
            ]
        );
        KabKota::updateOrCreate(
            ['nama' => 'Kab. Dompu'],
            [
                'provinsi_id' => $ntb->id,
                'nama_dinas' => 'DINAS PETERNAKAN DAN KESEHATAN HEWAN',
                'alamat_dinas' => 'DOMPU'
            ]
        );
        KabKota::updateOrCreate(
            ['nama' => 'Kab. Bima'],
            [
                'provinsi_id' => $ntb->id,
                'nama_dinas' => 'DINAS PETERNAKAN DAN KESEHATAN HEWAN',
                'alamat_dinas' => 'RABA - BIMA'
            ]
        );
        KabKota::updateOrCreate(
            ['nama' => 'Kota Bima'],
            [
                'provinsi_id' => $ntb->id,
                'nama_dinas' => 'DINAS PERTANIAN',
                'alamat_dinas' => 'BIMA'
            ]
        );
    }
}