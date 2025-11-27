<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Pengajuan;
use App\Models\Kuota;
use App\Models\PenggunaanKuota;
use App\Models\DokumenPengajuan;
use App\Models\KabKota;
use App\Models\JenisTernak;
use App\Models\TahapVerifikasi;
use App\Models\HistoriPengajuan;
use Illuminate\Support\Facades\Hash;

class ContohDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Membuat contoh data untuk semua alur...');

        // 1. Buat kuota untuk semua skenario
        $this->createKuotaData();

        // 2. Buat pengajuan untuk semua jenis dan status
        $this->createPengajuanData();

        // 3. Buat penggunaan kuota
        $this->createPenggunaanKuotaData();

        // 4. Buat dokumen pengajuan
        $this->createDokumenPengajuanData();

        $this->command->info('Contoh data berhasil dibuat!');
    }

    private function createKuotaData()
    {
        $this->command->info('Membuat data kuota...');

        // Ambil jenis ternak sapi pedaging
        $sapiPedaging = JenisTernak::where('nama', 'like', '%sapi%')->first();
        if (!$sapiPedaging) {
            $sapiPedaging = JenisTernak::first();
        }

        // Ambil kab/kota di Lombok dan Sumbawa
        $kabKotaLombok = KabKota::whereIn('nama', ['Lombok Barat', 'Lombok Tengah', 'Lombok Timur', 'Mataram'])->get();
        $kabKotaSumbawa = KabKota::whereIn('nama', ['Sumbawa', 'Sumbawa Barat', 'Bima', 'Dompu'])->get();
        
        // Jika tidak ada data, ambil kab/kota yang tersedia
        if ($kabKotaLombok->isEmpty()) {
            $kabKotaLombok = KabKota::take(4)->get();
        }
        if ($kabKotaSumbawa->isEmpty()) {
            $kabKotaSumbawa = KabKota::skip(4)->take(4)->get();
        }

        $tahun = date('Y');

        // Kuota untuk pulau Lombok (khusus)
        foreach ($kabKotaLombok as $kabKota) {
            // Kuota pemasukan
            Kuota::create([
                'jenis_ternak_id' => $sapiPedaging->id,
                'kab_kota_id' => $kabKota->id,
                'tahun' => $tahun,
                'kuota' => 100,
                'jenis_kuota' => 'pemasukan',
                'jenis_kelamin' => 'jantan',
                'pulau' => 'Lombok',
            ]);

            // Kuota pengeluaran
            Kuota::create([
                'jenis_ternak_id' => $sapiPedaging->id,
                'kab_kota_id' => $kabKota->id,
                'tahun' => $tahun,
                'kuota' => 150,
                'jenis_kuota' => 'pengeluaran',
                'jenis_kelamin' => 'jantan',
                'pulau' => 'Lombok',
            ]);
        }

        // Kuota untuk pulau Sumbawa (normal)
        foreach ($kabKotaSumbawa as $kabKota) {
            // Kuota pemasukan
            Kuota::create([
                'jenis_ternak_id' => $sapiPedaging->id,
                'kab_kota_id' => $kabKota->id,
                'tahun' => $tahun,
                'kuota' => 80,
                'jenis_kuota' => 'pemasukan',
                'jenis_kelamin' => 'jantan',
            ]);

            // Kuota pengeluaran
            Kuota::create([
                'jenis_ternak_id' => $sapiPedaging->id,
                'kab_kota_id' => $kabKota->id,
                'tahun' => $tahun,
                'kuota' => 120,
                'jenis_kuota' => 'pengeluaran',
                'jenis_kelamin' => 'jantan',
            ]);
        }
    }

    private function createPengajuanData()
    {
        $this->command->info('Membuat data pengajuan...');

        // Ambil data yang diperlukan
        $pengusaha = User::where('wewenang_id', 5)->first(); // Pengguna
        if (!$pengusaha) {
            $this->command->warn('Tidak ada user pengusaha, membuat user baru...');
            $pengusaha = User::create([
                'name' => 'Pengusaha Test',
                'email' => 'pengusaha@test.com',
                'password' => Hash::make('password'),
                'wewenang_id' => 5,
                'no_hp' => '081234567890',
                'alamat' => 'Jl. Test No. 1',
                'provinsi_verified_at' => now(),
            ]);
        }
        
        $sapiPedaging = JenisTernak::where('nama', 'like', '%sapi%')->first();
        if (!$sapiPedaging) {
            $sapiPedaging = JenisTernak::first();
        }
        
        $kabKotaLombok = KabKota::whereIn('nama', ['Lombok Barat', 'Lombok Tengah', 'Mataram'])->get();
        if ($kabKotaLombok->isEmpty()) {
            $kabKotaLombok = KabKota::take(3)->get();
        }
        
        $kabKotaSumbawa = KabKota::whereIn('nama', ['Sumbawa', 'Bima'])->get();
        if ($kabKotaSumbawa->isEmpty()) {
            $kabKotaSumbawa = KabKota::skip(3)->take(2)->get();
        }
        
        $tahapVerifikasi = TahapVerifikasi::orderBy('urutan')->get();

        $tahun = date('Y');

        // 1. Pengajuan Antar Kab/Kota - Status Menunggu
        $pengajuan1 = Pengajuan::create([
            'user_id' => $pengusaha->id,
            'jenis_pengajuan' => 'antar_kab_kota',
            'kategori_ternak_id' => $sapiPedaging->kategori_ternak_id,
            'jenis_kelamin' => 'jantan',
            'ras_ternak' => 'Sapi Bali',
            'jumlah_ternak' => 10,
            'tahun_pengajuan' => $tahun,
            'kab_kota_asal_id' => $kabKotaLombok->first()->id,
            'kab_kota_tujuan_id' => $kabKotaLombok->skip(1)->first()->id,
            'pelabuhan_asal' => 'Pelabuhan Lembar',
            'pelabuhan_tujuan' => 'Pelabuhan Kayangan',
            'jenis_ternak_id' => $sapiPedaging->id,
            'nomor_surat_permohonan' => 'SP/001/' . $tahun,
            'tanggal_surat_permohonan' => now()->subDays(5),
            'nomor_skkh' => 'SKKH/001/' . $tahun,
            'tahap_verifikasi_id' => $tahapVerifikasi[1]->id, // Disnak Kab/Kota Tujuan (urutan 2)
            'status' => 'menunggu',
        ]);

        // 2. Pengajuan Pengeluaran - Status Diproses
        $pengajuan2 = Pengajuan::create([
            'user_id' => $pengusaha->id,
            'jenis_pengajuan' => 'pengeluaran',
            'kategori_ternak_id' => $sapiPedaging->kategori_ternak_id,
            'jenis_kelamin' => 'jantan',
            'ras_ternak' => 'Sapi Ongole',
            'jumlah_ternak' => 15,
            'tahun_pengajuan' => $tahun,
            'kab_kota_asal_id' => $kabKotaLombok->first()->id,
            'provinsi_tujuan_id' => 1, // Asumsi provinsi luar NTB
            'kab_kota_tujuan' => 'Jakarta',
            'pelabuhan_asal' => 'Pelabuhan Lembar',
            'pelabuhan_tujuan' => 'Pelabuhan Tanjung Priok',
            'jenis_ternak_id' => $sapiPedaging->id,
            'nomor_surat_permohonan' => 'SP/002/' . $tahun,
            'tanggal_surat_permohonan' => now()->subDays(3),
            'nomor_skkh' => 'SKKH/002/' . $tahun,
            'tahap_verifikasi_id' => $tahapVerifikasi[3]->id, // Disnak Provinsi (urutan 4)
            'status' => 'diproses',
        ]);

        // 3. Pengajuan Pemasukan - Status Disetujui
        $pengajuan3 = Pengajuan::create([
            'user_id' => $pengusaha->id,
            'jenis_pengajuan' => 'pemasukan',
            'kategori_ternak_id' => $sapiPedaging->kategori_ternak_id,
            'jenis_kelamin' => 'jantan',
            'ras_ternak' => 'Sapi Brahman',
            'jumlah_ternak' => 20,
            'tahun_pengajuan' => $tahun,
            'provinsi_asal_id' => 1, // Asumsi provinsi luar NTB
            'kab_kota_asal' => 'Surabaya',
            'kab_kota_tujuan_id' => $kabKotaSumbawa->first()->id,
            'pelabuhan_asal' => 'Pelabuhan Tanjung Perak',
            'pelabuhan_tujuan' => 'Pelabuhan Sape',
            'jenis_ternak_id' => $sapiPedaging->id,
            'nomor_surat_permohonan' => 'SP/003/' . $tahun,
            'tanggal_surat_permohonan' => now()->subDays(7),
            'nomor_skkh' => 'SKKH/003/' . $tahun,
            'tahap_verifikasi_id' => $tahapVerifikasi[4]->id, // DPMPTSP
            'status' => 'disetujui',
        ]);

        // 4. Pengajuan Antar Kab/Kota Sumbawa - Status Ditolak
        $pengajuan4 = Pengajuan::create([
            'user_id' => $pengusaha->id,
            'jenis_pengajuan' => 'antar_kab_kota',
            'kategori_ternak_id' => $sapiPedaging->kategori_ternak_id,
            'jenis_kelamin' => 'jantan',
            'ras_ternak' => 'Sapi Madura',
            'jumlah_ternak' => 25,
            'tahun_pengajuan' => $tahun,
            'kab_kota_asal_id' => $kabKotaSumbawa->first()->id,
            'kab_kota_tujuan_id' => $kabKotaSumbawa->skip(1)->first()->id,
            'pelabuhan_asal' => 'Pelabuhan Sape',
            'pelabuhan_tujuan' => 'Pelabuhan Bima',
            'jenis_ternak_id' => $sapiPedaging->id,
            'nomor_surat_permohonan' => 'SP/004/' . $tahun,
            'tanggal_surat_permohonan' => now()->subDays(10),
            'nomor_skkh' => 'SKKH/004/' . $tahun,
            'tahap_verifikasi_id' => $tahapVerifikasi[0]->id, // Kembali ke pengusaha
            'status' => 'ditolak',
        ]);

        // 5. Pengajuan Pengeluaran Lombok - Status Selesai
        $pengajuan5 = Pengajuan::create([
            'user_id' => $pengusaha->id,
            'jenis_pengajuan' => 'pengeluaran',
            'kategori_ternak_id' => $sapiPedaging->kategori_ternak_id,
            'jenis_kelamin' => 'jantan',
            'ras_ternak' => 'Sapi Limousin',
            'jumlah_ternak' => 8,
            'tahun_pengajuan' => $tahun,
            'kab_kota_asal_id' => $kabKotaLombok->skip(2)->first()->id, // Mataram
            'provinsi_tujuan_id' => 1,
            'kab_kota_tujuan' => 'Bandung',
            'pelabuhan_asal' => 'Pelabuhan Kayangan',
            'pelabuhan_tujuan' => 'Pelabuhan Tanjung Priok',
            'jenis_ternak_id' => $sapiPedaging->id,
            'nomor_surat_permohonan' => 'SP/005/' . $tahun,
            'tanggal_surat_permohonan' => now()->subDays(15),
            'nomor_skkh' => 'SKKH/005/' . $tahun,
            'tahap_verifikasi_id' => $tahapVerifikasi[4]->id, // DPMPTSP
            'status' => 'selesai',
        ]);

        // Buat histori untuk pengajuan yang sudah diproses
        $this->createHistoriData([$pengajuan2, $pengajuan3, $pengajuan4, $pengajuan5]);
    }

    private function createHistoriData($pengajuans)
    {
        $this->command->info('Membuat data histori...');

        $users = User::whereIn('wewenang_id', [2, 3, 4])->get(); // Disnak Provinsi, Kab/Kota, DPMPTSP

        foreach ($pengajuans as $pengajuan) {
            // Histori untuk pengajuan yang diproses
            if ($pengajuan->status === 'diproses') {
                HistoriPengajuan::create([
                    'pengajuan_id' => $pengajuan->id,
                    'tahap_verifikasi_id' => $pengajuan->tahap_verifikasi_id,
                    'user_id' => $users->where('wewenang_id', 2)->first()->id, // Disnak Provinsi
                    'status' => 'disetujui',
                    'catatan' => 'Pengajuan telah disetujui dan diproses',
                ]);
            }

            // Histori untuk pengajuan yang disetujui
            if ($pengajuan->status === 'disetujui') {
                HistoriPengajuan::create([
                    'pengajuan_id' => $pengajuan->id,
                    'tahap_verifikasi_id' => $pengajuan->tahap_verifikasi_id,
                    'user_id' => $users->where('wewenang_id', 4)->first()->id, // DPMPTSP
                    'status' => 'disetujui',
                    'catatan' => 'Pengajuan telah diverifikasi dan disetujui',
                ]);
            }

            // Histori untuk pengajuan yang ditolak
            if ($pengajuan->status === 'ditolak') {
                HistoriPengajuan::create([
                    'pengajuan_id' => $pengajuan->id,
                    'tahap_verifikasi_id' => $pengajuan->tahap_verifikasi_id,
                    'user_id' => $users->where('wewenang_id', 3)->first()->id, // Disnak Kab/Kota
                    'status' => 'ditolak',
                    'alasan_penolakan' => 'Kuota sudah penuh untuk periode ini',
                ]);
            }

            // Histori untuk pengajuan yang selesai
            if ($pengajuan->status === 'selesai') {
                HistoriPengajuan::create([
                    'pengajuan_id' => $pengajuan->id,
                    'tahap_verifikasi_id' => $pengajuan->tahap_verifikasi_id,
                    'user_id' => $users->where('wewenang_id', 4)->first()->id, // DPMPTSP
                    'status' => 'disetujui',
                    'catatan' => 'Pengajuan telah selesai diproses',
                ]);
            }
        }
    }

    private function createPenggunaanKuotaData()
    {
        $this->command->info('Membuat data penggunaan kuota...');

        $pengajuans = Pengajuan::whereIn('status', ['disetujui', 'selesai'])->get();
        $tahun = date('Y');

        foreach ($pengajuans as $pengajuan) {
            // Ambil kuota yang sesuai
            $kuotaPemasukan = Kuota::where('tahun', $tahun)
                ->where('jenis_ternak_id', $pengajuan->jenis_ternak_id)
                ->where('kab_kota_id', $pengajuan->kab_kota_tujuan_id)
                ->where('jenis_kelamin', $pengajuan->jenis_kelamin)
                ->where('jenis_kuota', 'pemasukan')
                ->first();

            $kuotaPengeluaran = Kuota::where('tahun', $tahun)
                ->where('jenis_ternak_id', $pengajuan->jenis_ternak_id)
                ->where('kab_kota_id', $pengajuan->kab_kota_asal_id)
                ->where('jenis_kelamin', $pengajuan->jenis_kelamin)
                ->where('jenis_kuota', 'pengeluaran')
                ->first();

            // Catat penggunaan kuota pemasukan
            if ($kuotaPemasukan) {
                PenggunaanKuota::create([
                    'pengajuan_id' => $pengajuan->id,
                    'kuota_id' => $kuotaPemasukan->id,
                    'jumlah_digunakan' => $pengajuan->jumlah_ternak,
                    'jenis_penggunaan' => 'pemasukan',
                    'kab_kota_id' => $pengajuan->kab_kota_tujuan_id,
                    'tahun' => $tahun,
                    'jenis_ternak_id' => $pengajuan->jenis_ternak_id,
                    'jenis_kelamin' => $pengajuan->jenis_kelamin,
                    'pulau' => $kuotaPemasukan->pulau,
                ]);
            }

            // Catat penggunaan kuota pengeluaran
            if ($kuotaPengeluaran) {
                PenggunaanKuota::create([
                    'pengajuan_id' => $pengajuan->id,
                    'kuota_id' => $kuotaPengeluaran->id,
                    'jumlah_digunakan' => $pengajuan->jumlah_ternak,
                    'jenis_penggunaan' => 'pengeluaran',
                    'kab_kota_id' => $pengajuan->kab_kota_asal_id,
                    'tahun' => $tahun,
                    'jenis_ternak_id' => $pengajuan->jenis_ternak_id,
                    'jenis_kelamin' => $pengajuan->jenis_kelamin,
                    'pulau' => $kuotaPengeluaran->pulau,
                ]);
            }
        }
    }

    private function createDokumenPengajuanData()
    {
        $this->command->info('Membuat data dokumen pengajuan...');

        $pengajuans = Pengajuan::whereIn('status', ['disetujui', 'selesai'])->get();
        $users = User::whereIn('wewenang_id', [2, 3, 4])->get(); // Disnak Provinsi, Kab/Kota, DPMPTSP

        foreach ($pengajuans as $pengajuan) {
            // Dokumen dari dinas kab/kota asal
            if ($pengajuan->kab_kota_asal_id) {
                $userKabKotaAsal = $users->where('kab_kota_id', $pengajuan->kab_kota_asal_id)->first();
                if ($userKabKotaAsal) {
                    // Rekomendasi Keswan
                    DokumenPengajuan::create([
                        'pengajuan_id' => $pengajuan->id,
                        'user_id' => $userKabKotaAsal->id,
                        'jenis_dokumen' => 'rekomendasi_keswan',
                        'nama_file' => 'rekomendasi_keswan_' . $pengajuan->id . '.pdf',
                        'path_file' => 'dokumen-pengajuan/rekomendasi_keswan_' . $pengajuan->id . '.pdf',
                        'ukuran_file' => 1024000, // 1MB
                        'tipe_file' => 'pdf',
                        'keterangan' => 'Rekomendasi keswan dari dinas kab/kota asal',
                        'status' => 'aktif',
                    ]);

                    // SKKH
                    DokumenPengajuan::create([
                        'pengajuan_id' => $pengajuan->id,
                        'user_id' => $userKabKotaAsal->id,
                        'jenis_dokumen' => 'skkh',
                        'nama_file' => 'skkh_' . $pengajuan->id . '.pdf',
                        'path_file' => 'dokumen-pengajuan/skkh_' . $pengajuan->id . '.pdf',
                        'ukuran_file' => 2048000, // 2MB
                        'tipe_file' => 'pdf',
                        'keterangan' => 'SKKH dari dinas kab/kota asal',
                        'status' => 'aktif',
                    ]);

                    // Surat Keterangan Veteriner
                    DokumenPengajuan::create([
                        'pengajuan_id' => $pengajuan->id,
                        'user_id' => $userKabKotaAsal->id,
                        'jenis_dokumen' => 'surat_keterangan_veteriner',
                        'nama_file' => 'skv_' . $pengajuan->id . '.pdf',
                        'path_file' => 'dokumen-pengajuan/skv_' . $pengajuan->id . '.pdf',
                        'ukuran_file' => 1536000, // 1.5MB
                        'tipe_file' => 'pdf',
                        'keterangan' => 'Surat Keterangan Veteriner dari dinas kab/kota asal',
                        'status' => 'aktif',
                    ]);
                }
            }

            // Dokumen dari dinas kab/kota tujuan (hanya rekomendasi)
            if ($pengajuan->kab_kota_tujuan_id) {
                $userKabKotaTujuan = $users->where('kab_kota_id', $pengajuan->kab_kota_tujuan_id)->first();
                if ($userKabKotaTujuan) {
                    DokumenPengajuan::create([
                        'pengajuan_id' => $pengajuan->id,
                        'user_id' => $userKabKotaTujuan->id,
                        'jenis_dokumen' => 'rekomendasi_keswan',
                        'nama_file' => 'rekomendasi_tujuan_' . $pengajuan->id . '.pdf',
                        'path_file' => 'dokumen-pengajuan/rekomendasi_tujuan_' . $pengajuan->id . '.pdf',
                        'ukuran_file' => 1024000, // 1MB
                        'tipe_file' => 'pdf',
                        'keterangan' => 'Rekomendasi keswan dari dinas kab/kota tujuan',
                        'status' => 'aktif',
                    ]);
                }
            }

            // Dokumen dari disnak provinsi
            $userProvinsi = $users->where('wewenang_id', 2)->first();
            if ($userProvinsi) {
                DokumenPengajuan::create([
                    'pengajuan_id' => $pengajuan->id,
                    'user_id' => $userProvinsi->id,
                    'jenis_dokumen' => 'rekomendasi_keswan',
                    'nama_file' => 'rekomendasi_provinsi_' . $pengajuan->id . '.pdf',
                    'path_file' => 'dokumen-pengajuan/rekomendasi_provinsi_' . $pengajuan->id . '.pdf',
                    'ukuran_file' => 1024000, // 1MB
                    'tipe_file' => 'pdf',
                    'keterangan' => 'Rekomendasi keswan dari disnak provinsi',
                    'status' => 'aktif',
                ]);
            }

            // Dokumen dari DPMPTSP
            $userDpmptsp = $users->where('wewenang_id', 4)->first();
            if ($userDpmptsp) {
                $jenisIzin = $pengajuan->jenis_pengajuan === 'pengeluaran' ? 'izin_pengeluaran' : 'izin_pemasukan';
                $namaIzin = $pengajuan->jenis_pengajuan === 'pengeluaran' ? 'Izin Pengeluaran' : 'Izin Pemasukan';

                DokumenPengajuan::create([
                    'pengajuan_id' => $pengajuan->id,
                    'user_id' => $userDpmptsp->id,
                    'jenis_dokumen' => $jenisIzin,
                    'nama_file' => strtolower(str_replace(' ', '_', $namaIzin)) . '_' . $pengajuan->id . '.pdf',
                    'path_file' => 'dokumen-pengajuan/' . strtolower(str_replace(' ', '_', $namaIzin)) . '_' . $pengajuan->id . '.pdf',
                    'ukuran_file' => 2048000, // 2MB
                    'tipe_file' => 'pdf',
                    'keterangan' => $namaIzin . ' dari DPMPTSP',
                    'status' => 'aktif',
                ]);
            }
        }
    }
}