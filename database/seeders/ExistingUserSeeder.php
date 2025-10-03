<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\KabKota;
use App\Models\Wewenang;
use Illuminate\Support\Facades\Hash;

class ExistingUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get wewenang for 'Pengguna'
        $wewenangPengguna = Wewenang::where('nama', 'Pengguna')->first();
        
        // Get some kab/kota for variety
        $kabKotaList = KabKota::take(5)->get();

        $existingUsers = [
            [
                'name' => 'PT. Ternak Maju Jaya',
                'email' => 'ternak.maju.jaya@example.com',
                'password' => Hash::make('password123'),
                'no_hp' => '081234567890',
                'nama_perusahaan' => 'PT. Ternak Maju Jaya',
                'bidang_usaha' => 'hewan_ternak',
                'jenis_akun' => 'perusahaan',
                'nik' => '1234567890123456',
                'desa' => 'Desa Ternak',
                'alamat' => 'Jl. Peternakan No. 123',
                'telepon' => '0370-123456',
                'is_pernah_daftar' => true,
                'no_sp3' => 'SP3/001/2023',
                'no_register' => 'REG/001/2023',
                'tanggal_verifikasi' => now()->subMonths(6),
                'tanggal_berlaku' => now()->addYears(2)->subMonths(6),
            ],
            [
                'name' => 'CV. Sapi Sejahtera',
                'email' => 'sapi.sejahtera@example.com',
                'password' => Hash::make('password123'),
                'no_hp' => '081234567891',
                'nama_perusahaan' => 'CV. Sapi Sejahtera',
                'bidang_usaha' => 'hewan_ternak',
                'jenis_akun' => 'perusahaan',
                'nik' => '1234567890123457',
                'desa' => 'Desa Sapi',
                'alamat' => 'Jl. Peternakan Sapi No. 456',
                'telepon' => '0370-123457',
                'is_pernah_daftar' => true,
                'no_sp3' => 'SP3/002/2023',
                'no_register' => 'REG/002/2023',
                'tanggal_verifikasi' => now()->subMonths(4),
                'tanggal_berlaku' => now()->addYears(2)->subMonths(4),
            ],
            [
                'name' => 'Ahmad Ternak',
                'email' => 'ahmad.ternak@example.com',
                'password' => Hash::make('password123'),
                'no_hp' => '081234567892',
                'nama_perusahaan' => 'Ahmad Ternak',
                'bidang_usaha' => 'hewan_ternak',
                'jenis_akun' => 'perorangan',
                'nik' => '1234567890123458',
                'desa' => 'Desa Ternak Mandiri',
                'alamat' => 'Jl. Ternak Mandiri No. 789',
                'telepon' => '0370-123458',
                'is_pernah_daftar' => true,
                'no_sp3' => 'SP3/003/2023',
                'no_register' => 'REG/003/2023',
                'tanggal_verifikasi' => now()->subMonths(2),
                'tanggal_berlaku' => now()->addYears(2)->subMonths(2),
            ],
            [
                'name' => 'PT. Kambing Unggul',
                'email' => 'kambing.unggul@example.com',
                'password' => Hash::make('password123'),
                'no_hp' => '081234567893',
                'nama_perusahaan' => 'PT. Kambing Unggul',
                'bidang_usaha' => 'hewan_ternak',
                'jenis_akun' => 'perusahaan',
                'nik' => '1234567890123459',
                'desa' => 'Desa Kambing',
                'alamat' => 'Jl. Peternakan Kambing No. 321',
                'telepon' => '0370-123459',
                'is_pernah_daftar' => true,
                'no_sp3' => 'SP3/004/2023',
                'no_register' => 'REG/004/2023',
                'tanggal_verifikasi' => now()->subMonths(1),
                'tanggal_berlaku' => now()->addYears(2)->subMonths(1),
            ],
            [
                'name' => 'Budi Peternak',
                'email' => 'budi.peternak@example.com',
                'password' => Hash::make('password123'),
                'no_hp' => '081234567894',
                'nama_perusahaan' => 'Budi Peternak',
                'bidang_usaha' => 'gabungan_di_antaranya',
                'jenis_akun' => 'perorangan',
                'nik' => '1234567890123460',
                'desa' => 'Desa Peternakan',
                'alamat' => 'Jl. Peternakan Budi No. 654',
                'telepon' => '0370-123460',
                'is_pernah_daftar' => true,
                'no_sp3' => 'SP3/005/2023',
                'no_register' => 'REG/005/2023',
                'tanggal_verifikasi' => now()->subWeeks(2),
                'tanggal_berlaku' => now()->addYears(2)->subWeeks(2),
            ],
        ];

        foreach ($existingUsers as $index => $userData) {
            // Assign random kab/kota
            $userData['kab_kota_id'] = $kabKotaList[$index % $kabKotaList->count()]->id;
            $userData['wewenang_id'] = $wewenangPengguna->id;
            
            // Set verification status - some are already verified by provinsi
            if ($index < 3) {
                $userData['provinsi_verified_at'] = $userData['tanggal_verifikasi'];
                $userData['provinsi_verified_by'] = 2; // Disnak Provinsi user ID
            }
            
            User::create($userData);
        }

        $this->command->info('Created 5 existing users with is_pernah_daftar = true');
        $this->command->info('3 users are already verified by provinsi');
        $this->command->info('2 users are pending provinsi verification');
    }
}
