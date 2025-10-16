<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Models\DokumenPengajuan;

class DokumenDummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Membuat file dokumen dummy...');

        // Buat direktori jika belum ada
        if (!Storage::disk('public')->exists('dokumen-pengajuan')) {
            Storage::disk('public')->makeDirectory('dokumen-pengajuan');
        }

        // Ambil semua dokumen pengajuan
        $dokumens = DokumenPengajuan::all();

        foreach ($dokumens as $dokumen) {
            $this->createDummyFile($dokumen);
        }

        $this->command->info('File dokumen dummy berhasil dibuat!');
    }

    private function createDummyFile($dokumen)
    {
        $content = $this->generateDummyContent($dokumen);
        $path = $dokumen->path_file;

        // Buat file dummy
        Storage::disk('public')->put($path, $content);

        // Update ukuran file yang sebenarnya
        $dokumen->update([
            'ukuran_file' => strlen($content)
        ]);
    }

    private function generateDummyContent($dokumen)
    {
        $jenisDokumen = $dokumen->jenis_dokumen;
        $pengajuan = $dokumen->pengajuan;
        $uploader = $dokumen->user;

        $content = "=== DOKUMEN DUMMY ===\n\n";
        $content .= "Jenis Dokumen: " . $this->getJenisDokumenLabel($jenisDokumen) . "\n";
        $content .= "Nomor Surat: " . $pengajuan->nomor_surat_permohonan . "\n";
        $content .= "Jenis Pengajuan: " . ucfirst(str_replace('_', ' ', $pengajuan->jenis_pengajuan)) . "\n";
        $content .= "Jenis Ternak: " . $pengajuan->jenisTernak->nama . "\n";
        $content .= "Jumlah Ternak: " . $pengajuan->jumlah_ternak . " ekor\n";
        $content .= "Kab/Kota Asal: " . ($pengajuan->kabKotaAsal->nama ?? $pengajuan->kab_kota_asal) . "\n";
        $content .= "Kab/Kota Tujuan: " . ($pengajuan->kabKotaTujuan->nama ?? $pengajuan->kab_kota_tujuan) . "\n";
        $content .= "Uploaded by: " . $uploader->name . " (" . $uploader->wewenang->nama . ")\n";
        $content .= "Tanggal Upload: " . $dokumen->created_at->format('d/m/Y H:i') . "\n\n";

        // Konten spesifik berdasarkan jenis dokumen
        switch ($jenisDokumen) {
            case 'rekomendasi_keswan':
                $content .= "=== REKOMENDASI KESWAN ===\n\n";
                $content .= "Berdasarkan pemeriksaan kesehatan hewan yang telah dilakukan,\n";
                $content .= "kami merekomendasikan ternak tersebut untuk dapat dipindahkan\n";
                $content .= "dari " . ($pengajuan->kabKotaAsal->nama ?? $pengajuan->kab_kota_asal) . "\n";
                $content .= "ke " . ($pengajuan->kabKotaTujuan->nama ?? $pengajuan->kab_kota_tujuan) . ".\n\n";
                $content .= "Ternak dalam kondisi sehat dan bebas dari penyakit menular.\n";
                $content .= "Dokumen ini berlaku selama 7 hari sejak tanggal terbit.\n\n";
                break;

            case 'skkh':
                $content .= "=== SURAT KETERANGAN KESEHATAN HEWAN ===\n\n";
                $content .= "Dengan ini kami menyatakan bahwa ternak dengan spesifikasi:\n";
                $content .= "- Jenis: " . $pengajuan->jenisTernak->nama . "\n";
                $content .= "- Ras: " . $pengajuan->ras_ternak . "\n";
                $content .= "- Jenis Kelamin: " . ucfirst($pengajuan->jenis_kelamin) . "\n";
                $content .= "- Jumlah: " . $pengajuan->jumlah_ternak . " ekor\n\n";
                $content .= "Telah diperiksa dan dinyatakan SEHAT serta bebas dari penyakit menular.\n";
                $content .= "Nomor SKKH: " . $pengajuan->nomor_skkh . "\n\n";
                break;

            case 'surat_keterangan_veteriner':
                $content .= "=== SURAT KETERANGAN VETERINER ===\n\n";
                $content .= "Surat keterangan ini menyatakan bahwa ternak telah:\n";
                $content .= "1. Diperiksa oleh dokter hewan berwenang\n";
                $content .= "2. Dinyatakan sehat dan layak untuk dipindahkan\n";
                $content .= "3. Telah divaksinasi sesuai protokol kesehatan\n";
                $content .= "4. Memiliki identifikasi yang jelas\n\n";
                $content .= "Surat ini berlaku selama 5 hari sejak tanggal terbit.\n\n";
                break;

            case 'izin_pengeluaran':
                $content .= "=== IZIN PENGELUARAN TERNAK ===\n\n";
                $content .= "Berdasarkan permohonan nomor " . $pengajuan->nomor_surat_permohonan . ",\n";
                $content .= "kami memberikan izin untuk mengeluarkan ternak dari wilayah NTB\n";
                $content .= "ke " . $pengajuan->kab_kota_tujuan . ".\n\n";
                $content .= "Izin ini berlaku selama 10 hari sejak tanggal terbit.\n";
                $content .= "Pemohon wajib melaporkan keberangkatan ternak minimal 24 jam sebelumnya.\n\n";
                break;

            case 'izin_pemasukan':
                $content .= "=== IZIN PEMASUKAN TERNAK ===\n\n";
                $content .= "Berdasarkan permohonan nomor " . $pengajuan->nomor_surat_permohonan . ",\n";
                $content .= "kami memberikan izin untuk memasukkan ternak dari " . $pengajuan->kab_kota_asal . "\n";
                $content .= "ke wilayah NTB.\n\n";
                $content .= "Izin ini berlaku selama 10 hari sejak tanggal terbit.\n";
                $content .= "Pemohon wajib melaporkan kedatangan ternak minimal 24 jam sebelumnya.\n\n";
                break;

            case 'dokumen_lainnya':
                $content .= "=== DOKUMEN PENDUKUNG ===\n\n";
                $content .= "Dokumen pendukung untuk pengajuan nomor " . $pengajuan->nomor_surat_permohonan . ".\n";
                $content .= "Dokumen ini berisi informasi tambahan yang diperlukan\n";
                $content .= "untuk melengkapi proses pengajuan.\n\n";
                break;
        }

        $content .= "=== INFORMASI TEKNIS ===\n";
        $content .= "File: " . $dokumen->nama_file . "\n";
        $content .= "Path: " . $dokumen->path_file . "\n";
        $content .= "Ukuran: " . $dokumen->ukuran_file_display . "\n";
        $content .= "Status: " . ucfirst($dokumen->status) . "\n\n";

        if ($dokumen->keterangan) {
            $content .= "=== KETERANGAN ===\n";
            $content .= $dokumen->keterangan . "\n\n";
        }

        $content .= "=== FOOTER ===\n";
        $content .= "Dokumen ini dibuat secara otomatis untuk keperluan testing.\n";
        $content .= "Tanggal Generate: " . now()->format('d/m/Y H:i:s') . "\n";

        return $content;
    }

    private function getJenisDokumenLabel($jenis)
    {
        return match ($jenis) {
            'rekomendasi_keswan' => 'Rekomendasi Keswan',
            'skkh' => 'Surat Keterangan Kesehatan Hewan',
            'surat_keterangan_veteriner' => 'Surat Keterangan Veteriner',
            'izin_pengeluaran' => 'Izin Pengeluaran',
            'izin_pemasukan' => 'Izin Pemasukan',
            'dokumen_lainnya' => 'Dokumen Lainnya',
            default => ucfirst(str_replace('_', ' ', $jenis)),
        };
    }
}