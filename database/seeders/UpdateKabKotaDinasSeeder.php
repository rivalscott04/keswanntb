<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\KabKota;

class UpdateKabKotaDinasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataDinas = [
            'Kota Mataram' => [
                'nama_dinas' => 'DINAS PERTANIAN',
                'alamat_dinas' => 'MATARAM'
            ],
            'Kab. Lombok Barat' => [
                'nama_dinas' => 'DINAS PERTANIAN',
                'alamat_dinas' => 'LABUAPI'
            ],
            'Kab. Lombok Tengah' => [
                'nama_dinas' => 'DINAS PERTANIAN',
                'alamat_dinas' => 'PRAYA'
            ],
            'Kab. Lombok Timur' => [
                'nama_dinas' => 'DINAS PETERNAKAN DAN KESEHATAN HEWAN',
                'alamat_dinas' => 'SELONG'
            ],
            'Kab. Lombok Utara' => [
                'nama_dinas' => 'DINAS KETAHANAN PANGAN PERTANIAN DAN PERIKANAN',
                'alamat_dinas' => 'GONDANG'
            ],
            'Kab. Sumbawa Barat' => [
                'nama_dinas' => 'DINAS PERTANIAN',
                'alamat_dinas' => 'TALIWANG'
            ],
            'Kab. Sumbawa' => [
                'nama_dinas' => 'DINAS PETERNAKAN DAN KESEHATAN HEWAN',
                'alamat_dinas' => 'SUMBAWA BESAR'
            ],
            'Kab. Dompu' => [
                'nama_dinas' => 'DINAS PETERNAKAN DAN KESEHATAN HEWAN',
                'alamat_dinas' => 'DOMPU'
            ],
            'Kab. Bima' => [
                'nama_dinas' => 'DINAS PETERNAKAN DAN KESEHATAN HEWAN',
                'alamat_dinas' => 'RABA - BIMA'
            ],
            'Kota Bima' => [
                'nama_dinas' => 'DINAS PERTANIAN',
                'alamat_dinas' => 'BIMA'
            ],
        ];

        foreach ($dataDinas as $namaKabKota => $data) {
            KabKota::where('nama', $namaKabKota)->update([
                'nama_dinas' => $data['nama_dinas'],
                'alamat_dinas' => $data['alamat_dinas']
            ]);
        }

        $this->command->info('Data dinas kab/kota berhasil diupdate!');
    }
}
