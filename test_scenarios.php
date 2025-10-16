<?php

/**
 * Script Testing Skenario Sistem Pengajuan Ternak NTB
 * 
 * Script ini digunakan untuk testing semua skenario yang tersedia
 * dalam sistem pengajuan ternak NTB.
 */

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\Pengajuan;
use App\Models\DokumenPengajuan;
use App\Models\PenggunaanKuota;
use App\Models\User;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 TESTING SKENARIO SISTEM PENGAJUAN TERNAK NTB\n";
echo "================================================\n\n";

// Test 1: Cek Data Pengajuan
echo "📋 TEST 1: Data Pengajuan\n";
echo "-------------------------\n";
$pengajuans = Pengajuan::with(['user', 'kabKotaAsal', 'kabKotaTujuan', 'jenisTernak'])->get();
foreach ($pengajuans as $pengajuan) {
    echo "• {$pengajuan->nomor_surat_permohonan} - {$pengajuan->jenis_pengajuan} - {$pengajuan->status}\n";
    echo "  Asal: " . ($pengajuan->kabKotaAsal->nama ?? $pengajuan->kab_kota_asal) . "\n";
    echo "  Tujuan: " . ($pengajuan->kabKotaTujuan->nama ?? $pengajuan->kab_kota_tujuan) . "\n";
    echo "  Jumlah: {$pengajuan->jumlah_ternak} ekor\n";
    echo "  Tahap: {$pengajuan->tahapVerifikasi->nama}\n\n";
}

// Test 2: Cek Penggunaan Kuota
echo "📊 TEST 2: Penggunaan Kuota\n";
echo "----------------------------\n";
$penggunaanKuota = PenggunaanKuota::with(['pengajuan', 'kabKota'])->get();
foreach ($penggunaanKuota as $penggunaan) {
    echo "• Pengajuan: {$penggunaan->pengajuan->nomor_surat_permohonan}\n";
    echo "  Jenis: {$penggunaan->jenis_penggunaan}\n";
    echo "  Kab/Kota: {$penggunaan->kabKota->nama}\n";
    echo "  Digunakan: {$penggunaan->jumlah_digunakan} ekor\n";
    echo "  Pulau: " . ($penggunaan->pulau ?? 'Tidak ada') . "\n\n";
}

// Test 3: Cek Dokumen Pengajuan
echo "📄 TEST 3: Dokumen Pengajuan\n";
echo "-----------------------------\n";
$dokumens = DokumenPengajuan::with(['pengajuan', 'user'])->aktif()->get();
foreach ($dokumens as $dokumen) {
    echo "• {$dokumen->pengajuan->nomor_surat_permohonan}\n";
    echo "  Jenis: {$dokumen->jenis_dokumen}\n";
    echo "  File: {$dokumen->nama_file}\n";
    echo "  Uploader: {$dokumen->user->name} ({$dokumen->user->wewenang->nama})\n";
    echo "  Ukuran: {$dokumen->ukuran_file_display}\n\n";
}

// Test 4: Cek User dan Wewenang
echo "👥 TEST 4: User dan Wewenang\n";
echo "-----------------------------\n";
$users = User::with('wewenang')->get();
foreach ($users as $user) {
    echo "• {$user->name} ({$user->email})\n";
    echo "  Wewenang: {$user->wewenang->nama}\n";
    echo "  Kab/Kota: " . ($user->kabKota->nama ?? 'Tidak ada') . "\n";
    echo "  Status: " . ($user->provinsi_verified_at ? 'Terverifikasi' : 'Belum terverifikasi') . "\n\n";
}

// Test 5: Cek Kuota Tersisa
echo "🎯 TEST 5: Kuota Tersisa\n";
echo "------------------------\n";
$kuotas = DB::table('kuota')
    ->join('kab_kota', 'kuota.kab_kota_id', '=', 'kab_kota.id')
    ->join('jenis_ternak', 'kuota.jenis_ternak_id', '=', 'jenis_ternak.id')
    ->select('kuota.*', 'kab_kota.nama as kab_kota_nama', 'jenis_ternak.nama as jenis_ternak_nama')
    ->get();

foreach ($kuotas as $kuota) {
    $digunakan = PenggunaanKuota::where('tahun', $kuota->tahun)
        ->where('jenis_ternak_id', $kuota->jenis_ternak_id)
        ->where('kab_kota_id', $kuota->kab_kota_id)
        ->where('jenis_kelamin', $kuota->jenis_kelamin)
        ->where('jenis_penggunaan', $kuota->jenis_kuota)
        ->sum('jumlah_digunakan');
    
    $tersisa = $kuota->kuota - $digunakan;
    
    echo "• {$kuota->kab_kota_nama} - {$kuota->jenis_ternak_nama}\n";
    echo "  Jenis: {$kuota->jenis_kuota}\n";
    echo "  Total: {$kuota->kuota} ekor\n";
    echo "  Digunakan: {$digunakan} ekor\n";
    echo "  Tersisa: {$tersisa} ekor\n";
    echo "  Pulau: " . ($kuota->pulau ?? 'Tidak ada') . "\n\n";
}

// Test 6: Cek Workflow
echo "🔄 TEST 6: Workflow Pengajuan\n";
echo "------------------------------\n";
$workflows = [
    'antar_kab_kota' => 'Pengusaha → Disnak Kab/Kota Asal → Disnak Kab/Kota Tujuan → Disnak Provinsi → DPMPTSP',
    'pengeluaran' => 'Pengusaha → Disnak Kab/Kota Asal → Disnak Provinsi → DPMPTSP',
    'pemasukan' => 'Pengusaha → Disnak Kab/Kota Tujuan → Disnak Provinsi → DPMPTSP',
];

foreach ($workflows as $jenis => $workflow) {
    echo "• {$jenis}: {$workflow}\n";
}
echo "\n";

// Test 7: Cek Skenario Khusus Lombok
echo "🏝️ TEST 7: Skenario Khusus Lombok\n";
echo "----------------------------------\n";
$kuotaLombok = DB::table('kuota')
    ->join('kab_kota', 'kuota.kab_kota_id', '=', 'kab_kota.id')
    ->where('kuota.pulau', 'Lombok')
    ->select('kuota.*', 'kab_kota.nama as kab_kota_nama')
    ->get();

echo "Kuota Lombok (Terintegrasi):\n";
foreach ($kuotaLombok as $kuota) {
    echo "• {$kuota->kab_kota_nama}: {$kuota->kuota} ekor ({$kuota->jenis_kuota})\n";
}
echo "\n";

// Test 8: Cek File Dokumen
echo "📁 TEST 8: File Dokumen\n";
echo "------------------------\n";
$storagePath = storage_path('app/public/dokumen-pengajuan');
if (is_dir($storagePath)) {
    $files = scandir($storagePath);
    $fileCount = count($files) - 2; // Exclude . and ..
    echo "• Jumlah file dokumen: {$fileCount}\n";
    echo "• Path: {$storagePath}\n";
    
    if ($fileCount > 0) {
        echo "• File contoh:\n";
        $sampleFiles = array_slice($files, 2, 3); // Ambil 3 file pertama
        foreach ($sampleFiles as $file) {
            if ($file !== '.' && $file !== '..') {
                $filePath = $storagePath . '/' . $file;
                $fileSize = filesize($filePath);
                echo "  - {$file} (" . number_format($fileSize) . " bytes)\n";
            }
        }
    }
} else {
    echo "• Direktori dokumen tidak ditemukan\n";
}
echo "\n";

// Summary
echo "📈 SUMMARY\n";
echo "==========\n";
echo "• Total Pengajuan: " . Pengajuan::count() . "\n";
echo "• Total Dokumen: " . DokumenPengajuan::count() . "\n";
echo "• Total Penggunaan Kuota: " . PenggunaanKuota::count() . "\n";
echo "• Total User: " . User::count() . "\n";
echo "• Total Kuota: " . DB::table('kuota')->count() . "\n\n";

echo "✅ Testing selesai! Semua skenario telah dicek.\n";
echo "📖 Lihat file SCENARIO_TESTING.md untuk panduan lengkap.\n";
