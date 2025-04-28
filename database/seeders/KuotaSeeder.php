<?php

namespace Database\Seeders;

use App\Models\Kuota;
use Illuminate\Database\Seeder;
use App\Models\JenisTernak;
use App\Models\KabKota;

class KuotaSeeder extends Seeder
{
    public function run(): void
    {
        $tahun = 2025;
        $jenisKelamin = ['jantan', 'betina'];
        $jenisKuota = ['pemasukan', 'pengeluaran'];
        $jenisTernaks = JenisTernak::all();
        $kabKotas = KabKota::all();

        foreach ($jenisTernaks as $jenisTernak) {
            foreach ($kabKotas as $kabKota) {
                foreach ($jenisKelamin as $kelamin) {
                    foreach ($jenisKuota as $jkuota) {
                        \App\Models\Kuota::create([
                            'jenis_ternak_id' => $jenisTernak->id,
                            'kab_kota_id' => $kabKota->id,
                            'tahun' => $tahun,
                            'kuota' => rand(10, 100),
                            'jenis_kuota' => $jkuota,
                            'jenis_kelamin' => $kelamin,
                            'pulau' => '',
                        ]);
                    }
                }
            }
        }
    }
}