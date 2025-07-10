<?php

namespace Database\Seeders;

use App\Models\HistoriSp3;
use App\Models\Sp3;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HistoriSp3Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'pengusaha@example.com')->first();
        $sp3 = Sp3::first();
        if ($user && $sp3) {
            HistoriSp3::create([
                'sp3_id' => $sp3->id,
                'user_id' => $user->id,
                'status' => 'draft',
                'catatan' => 'Pengajuan awal dibuat oleh user.',
                'data_sebelum' => null,
                'data_sesudah' => null,
            ]);
        }
    }
}
