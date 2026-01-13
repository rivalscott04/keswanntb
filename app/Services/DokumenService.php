<?php

namespace App\Services;

use App\Models\Pengajuan;
use App\Models\DokumenPengajuan;
use App\Models\Pengaturan;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DokumenService
{
    /**
     * Mapping template file berdasarkan jenis dokumen dan jenis pengajuan
     */
    private static function getTemplatePath($jenisDokumen, $jenisPengajuan = null): ?string
    {
        $templates = [
            // Rekomendasi berdasarkan jenis pengajuan
            'rekomendasi_keswan' => [
                'pemasukan' => public_path('docs/DRAFT PEmasukan.docx'),
                'pengeluaran' => public_path('docs/DRAFT PENGAJUAN.docx'),
                'antar_kab_kota' => public_path('docs/DRAFT PENGAJUAN.docx'),
                'default' => public_path('docs/DRAFT PENGAJUAN.docx'),
            ],
            'skkh' => [
                'default' => public_path('docs/DRAFT PENGAJUAN.docx'),
            ],
            'surat_keterangan_veteriner' => [
                'default' => public_path('docs/DRAFT PENGAJUAN.docx'),
            ],
            'izin_pengeluaran' => [
                'default' => public_path('docs/REKOM PENGELUARAN UNGGAS DOC KE LUAR DAERAH.pdf'),
            ],
            'izin_pemasukan' => [
                'default' => public_path('docs/REKOM PEMASUKAN UNGGAS DOC DARI LUAR DAERAH.pdf'),
            ],
        ];

        $templateConfig = $templates[$jenisDokumen] ?? null;
        if (!$templateConfig) {
            return null;
        }

        // Cek apakah ada template spesifik untuk jenis pengajuan
        if ($jenisPengajuan && isset($templateConfig[$jenisPengajuan])) {
            $templatePath = $templateConfig[$jenisPengajuan];
        } else {
            $templatePath = $templateConfig['default'] ?? null;
        }

        // Cek apakah file template ada
        if ($templatePath && file_exists($templatePath)) {
            return $templatePath;
        }

        return null;
    }

    /**
     * Generate dokumen dari template dengan data pengajuan
     */
    public static function generateDokumen(Pengajuan $pengajuan, string $jenisDokumen, $userId = null): ?DokumenPengajuan
    {
        $userId = $userId ?? auth()->id();

        // Get template path
        $templatePath = self::getTemplatePath($jenisDokumen, $pengajuan->jenis_pengajuan);
        
        if (!$templatePath) {
            throw new \Exception("Template untuk jenis dokumen '{$jenisDokumen}' tidak ditemukan.");
        }

        // Cek apakah file template adalah .docx (TemplateProcessor hanya mendukung .docx)
        $extension = strtolower(pathinfo($templatePath, PATHINFO_EXTENSION));
        
        if ($extension !== 'docx') {
            throw new \Exception("Template harus berupa file Word (.docx). File yang ditemukan: {$extension}");
        }

        // Cek apakah file template ada dan bisa dibaca
        if (!file_exists($templatePath)) {
            throw new \Exception("File template tidak ditemukan: {$templatePath}");
        }

        if (!is_readable($templatePath)) {
            throw new \Exception("File template tidak bisa dibaca: {$templatePath}");
        }

        try {
            // Load template
            $template = new TemplateProcessor($templatePath);

            // Prepare data untuk template
            $data = self::prepareTemplateData($pengajuan, $jenisDokumen);

            // Set values ke template
            $template->setValues($data);

            // Generate nama file output
            $outputFileName = self::generateFileName($pengajuan, $jenisDokumen);
            $outputPath = storage_path('app/public/dokumen-pengajuan/' . $outputFileName);

            // Pastikan directory ada
            $directory = dirname($outputPath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            // Save dokumen
            $template->saveAs($outputPath);

            // Get file size
            $fileSize = filesize($outputPath);

            // PENTING: Hanya nonaktifkan dokumen yang di-generate dari user yang SAMA
            // JANGAN PERNAH nonaktifkan dokumen yang di-upload manual (keterangan tidak mengandung "di-generate otomatis")
            // JANGAN PERNAH nonaktifkan dokumen dari user lain
            $dokumenLama = DokumenPengajuan::where('pengajuan_id', $pengajuan->id)
                ->where('user_id', $userId) // HANYA dari user yang sama
                ->where('jenis_dokumen', $jenisDokumen)
                ->where('status', 'aktif')
                ->where(function($q) {
                    // Hanya dokumen yang di-generate (bukan manual)
                    $q->where('keterangan', 'like', '%di-generate otomatis%')
                      ->orWhereNull('keterangan'); // Jika null, kemungkinan di-generate juga
                })
                ->get();
            
            if ($dokumenLama->isNotEmpty()) {
                // Nonaktifkan dokumen lama yang di-generate dari user yang sama
                // INI AMAN karena hanya menonaktifkan dokumen generate dari user yang sama
                foreach ($dokumenLama as $doc) {
                    // Double check: pastikan ini bukan dokumen manual
                    if (str_contains($doc->keterangan ?? '', 'di-generate otomatis') || empty($doc->keterangan)) {
                        $doc->update(['status' => 'tidak_aktif']);
                    }
                }
            }

            // Create DokumenPengajuan record
            $dokumenPengajuan = DokumenPengajuan::create([
                'pengajuan_id' => $pengajuan->id,
                'user_id' => $userId,
                'jenis_dokumen' => $jenisDokumen,
                'nama_file' => $outputFileName,
                'path_file' => 'dokumen-pengajuan/' . $outputFileName,
                'ukuran_file' => $fileSize,
                'tipe_file' => 'docx',
                'keterangan' => "Dokumen di-generate otomatis dari template pada " . now()->format('d/m/Y H:i:s'),
                'status' => 'aktif',
            ]);

            return $dokumenPengajuan;
        } catch (\Exception $e) {
            throw new \Exception("Gagal generate dokumen: " . $e->getMessage());
        }
    }

    /**
     * Prepare data untuk template dari pengajuan
     */
    private static function prepareTemplateData(Pengajuan $pengajuan, string $jenisDokumen): array
    {
        // Load biodata kadis dari pengaturan
        $biodataKadis = null;
        $pengaturanKadis = Pengaturan::firstWhere('key', 'biodata_kadis');
        if ($pengaturanKadis) {
            $biodataKadis = json_decode($pengaturanKadis->value);
        }

        // Ambil tanggal approval (ketika status menjadi "disetujui")
        // Cari dari histori pengajuan atau gunakan updated_at jika status sudah disetujui
        $tanggalApproval = null;
        if ($pengajuan->status === 'disetujui' || $pengajuan->status === 'selesai') {
            // Cari dari histori pengajuan ketika status menjadi "disetujui" oleh Disnak Provinsi (urutan 4)
            $historiApproval = \App\Models\HistoriPengajuan::where('pengajuan_id', $pengajuan->id)
                ->where('status', 'disetujui')
                ->whereHas('tahapVerifikasi', function($q) {
                    $q->where('urutan', 4); // Disnak Provinsi
                })
                ->orderBy('created_at', 'desc')
                ->first();
            
            if ($historiApproval) {
                $tanggalApproval = Carbon::parse($historiApproval->created_at);
            } else {
                // Fallback ke updated_at pengajuan jika histori tidak ditemukan
                $tanggalApproval = Carbon::parse($pengajuan->updated_at);
            }
        }
        
        // Jika belum approval, gunakan tanggal sekarang (untuk preview)
        $tanggalSurat = $tanggalApproval ?? Carbon::now();
        $tanggalSekarang = Carbon::now();
        
        // Hitung tanggal berlaku: 14 hari setelah approval
        $tanggalBerlakuAwal = $tanggalSurat;
        $tanggalBerlakuAkhir = $tanggalSurat->copy()->addDays(14);
        
        // Format tanggal surat permohonan
        $tanggalSuratPermohonan = $pengajuan->tanggal_surat_permohonan 
            ? Carbon::parse($pengajuan->tanggal_surat_permohonan) 
            : $tanggalSekarang;
        
        // Ambil satuan dari pengajuan
        $satuan = $pengajuan->satuan ?? 'ekor';
        
        // Format jumlah dengan satuan: "80 Ekor" atau "100 Kg" dll
        $jumlahDenganSatuan = number_format($pengajuan->jumlah_ternak, 0, ',', '.') . ' ' . ucfirst($satuan);

        // Data dasar
        $data = [
            // Informasi pemohon/perusahaan
            'nama_perusahaan' => $pengajuan->user->nama_perusahaan ?? $pengajuan->user->name ?? '-',
            'nama_pemohon' => $pengajuan->user->name ?? '-',
            'alamat_perusahaan' => $pengajuan->user->alamat ?? '-',
            'alamat' => $pengajuan->user->alamat ?? '-', // Alias
            'desa' => $pengajuan->user->desa ?? '-',
            'telepon' => $pengajuan->user->telepon ?? $pengajuan->user->no_hp ?? '-',
            'no_hp' => $pengajuan->user->no_hp ?? $pengajuan->user->telepon ?? '-',
            'email' => $pengajuan->user->email ?? '-',
            'no_nib' => $pengajuan->user->no_nib ?? '-',
            'no_npwp' => $pengajuan->user->no_npwp ?? '-',
            
            // Informasi pengajuan
            'nomor_surat_permohonan' => $pengajuan->nomor_surat_permohonan ?? '-',
            'nomor_surat' => $pengajuan->nomor_surat_permohonan ?? '-', // Alias
            'tanggal_surat_permohonan' => $tanggalSuratPermohonan->translatedFormat('d F Y'),
            'tanggal_surat' => $tanggalSuratPermohonan->translatedFormat('d F Y'), // Alias
            'jenis_pengajuan' => match($pengajuan->jenis_pengajuan) {
                'antar_kab_kota' => 'Antar Kabupaten/Kota',
                'pengeluaran' => 'Pengeluaran',
                'pemasukan' => 'Pemasukan',
                default => $pengajuan->jenis_pengajuan,
            },
            'tahun_pengajuan' => $pengajuan->tahun_pengajuan ?? date('Y'),
            
            // Informasi ternak
            'jenis_ternak' => $pengajuan->jenisTernak->nama ?? '-',
            'kategori_ternak' => $pengajuan->jenisTernak->kategoriTernak->nama ?? '-',
            'jumlah_ternak' => $jumlahDenganSatuan, // Format: "80 Ekor" atau "100 Kg"
            'jumlah' => $jumlahDenganSatuan, // Alias
            'jenis_kelamin' => ucfirst($pengajuan->jenis_kelamin),
            'jeniskelamin' => ucfirst($pengajuan->jenis_kelamin), // Alias tanpa underscore (sesuai template)
            'ras_ternak' => $pengajuan->ras_ternak ?? '-',
            'ras' => $pengajuan->ras_ternak ?? '-', // Alias
            'satuan' => ucfirst($satuan),
            
            // Lokasi asal
            'provinsi_asal' => $pengajuan->provinsiAsal->nama ?? $pengajuan->provinsi_asal ?? '-',
            'kab_kota_asal' => $pengajuan->kabKotaAsal->nama ?? $pengajuan->kab_kota_asal ?? '-',
            'kabupaten_asal' => $pengajuan->kabKotaAsal->nama ?? $pengajuan->kab_kota_asal ?? '-', // Alias
            'pelabuhan_asal' => $pengajuan->pelabuhan_asal ?? '-',
            
            // Lokasi tujuan
            'provinsi_tujuan' => $pengajuan->provinsiTujuan->nama ?? $pengajuan->provinsi_tujuan ?? '-',
            'kab_kota_tujuan' => $pengajuan->kabKotaTujuan->nama ?? $pengajuan->kab_kota_tujuan ?? '-',
            'kab_kota_tujua' => $pengajuan->kabKotaTujuan->nama ?? $pengajuan->kab_kota_tujuan ?? '-', // Handle typo di template
            'kabupaten_tujuan' => $pengajuan->kabKotaTujuan->nama ?? $pengajuan->kab_kota_tujuan ?? '-', // Alias
            'pelabuhan_tujuan' => $pengajuan->pelabuhan_tujuan ?? '-',
            
            // Placeholder tambahan dari template
            'noperusahaan' => $pengajuan->user->no_nib ?? $pengajuan->user->no_npwp ?? '-', // Nomor perusahaan (NIB atau NPWP)
            'jmlhari' => $tanggalBerlakuAwal->translatedFormat('d F Y') . ' s/d ' . $tanggalBerlakuAkhir->translatedFormat('d F Y'), // Format: "9 Januari 2026 s/d 22 Januari 2026"
            
            // Tanggal dokumen (setelah approval)
            'tanggal_dokumen' => $tanggalSurat->translatedFormat('d F Y'),
            'tanggal' => $tanggalSurat->translatedFormat('d F Y'), // Alias
            'tanggal_ttd' => $tanggalSurat->translatedFormat('d F Y'),
            'tanggal_sekarang' => $tanggalSurat->translatedFormat('d F Y'), // Alias
            
            // Biodata kadis
            'nama_kadis' => $biodataKadis->nama ?? '-',
            'namakadis' => $biodataKadis->nama ?? '-', // Alias tanpa underscore
            'pangkat_kadis' => $biodataKadis->jabatan ?? '-',
            'jabatan_kadis' => $biodataKadis->jabatan ?? '-', // Alias
            'golongan_kadis' => $biodataKadis->golongan ?? $biodataKadis->jabatan ?? '-', // Golongan kadis (jika ada field terpisah, jika tidak ambil dari jabatan)
            'golongankadis' => $biodataKadis->golongan ?? $biodataKadis->jabatan ?? '-', // Alias tanpa underscore
            'nip_kadis' => $biodataKadis->nip ?? '-',
            'nipkadis' => $biodataKadis->nip ?? '-', // Alias tanpa underscore
            'nip' => $biodataKadis->nip ?? '-', // Alias
            'nip_kadis_formatted' => $biodataKadis->nip ? 'NIP. ' . $biodataKadis->nip : '-', // Format dengan "NIP. " di depan
            'nipkadis_formatted' => $biodataKadis->nip ? 'NIP. ' . $biodataKadis->nip : '-', // Alias tanpa underscore dengan format
        ];

        // Tambahkan data khusus berdasarkan jenis dokumen
        switch ($jenisDokumen) {
            case 'rekomendasi_keswan':
                // Nomor surat dikosongkan (diisi manual setelah TTD kadis)
                $data['nomor_dokumen'] = ''; // Kosongkan, akan diisi manual setelah TTD
                break;
            
            case 'skkh':
                $data['nomor_skkh'] = $pengajuan->nomor_skkh ?? '-';
                break;
            
            case 'izin_pengeluaran':
            case 'izin_pemasukan':
                $data['nomor_izin'] = self::generateNomorDokumen($pengajuan, 'IZIN');
                break;
        }

        // Handle jumlah jantan dan betina jika ada
        if ($pengajuan->jumlah_jantan) {
            $data['jumlah_jantan'] = number_format($pengajuan->jumlah_jantan, 0, ',', '.');
            $data['jantan'] = number_format($pengajuan->jumlah_jantan, 0, ',', '.'); // Alias
        } else {
            $data['jumlah_jantan'] = '-';
            $data['jantan'] = '-';
        }
        
        if ($pengajuan->jumlah_betina) {
            $data['jumlah_betina'] = number_format($pengajuan->jumlah_betina, 0, ',', '.');
            $data['betina'] = number_format($pengajuan->jumlah_betina, 0, ',', '.'); // Alias
        } else {
            $data['jumlah_betina'] = '-';
            $data['betina'] = '-';
        }
        
        // Tambahkan informasi tambahan yang mungkin dibutuhkan
        $data['keterangan'] = $pengajuan->keterangan ?? '-';
        $data['status'] = ucfirst($pengajuan->status ?? 'menunggu');

        return $data;
    }

    /**
     * Generate nomor dokumen
     */
    private static function generateNomorDokumen(Pengajuan $pengajuan, string $prefix): string
    {
        $tahun = $pengajuan->tahun_pengajuan ?? date('Y');
        $kabKota = $pengajuan->kabKotaAsal->nama ?? $pengajuan->kabKotaTujuan->nama ?? 'NTB';
        
        // Ambil inisial kab/kota (2-3 huruf pertama)
        $inisialKabKota = strtoupper(substr(str_replace(['Kab.', 'Kota', ' '], '', $kabKota), 0, 3));
        
        // Generate nomor urut (bisa dari counter atau ID pengajuan)
        $nomorUrut = str_pad($pengajuan->id, 4, '0', STR_PAD_LEFT);
        
        return "{$prefix}/{$inisialKabKota}/{$tahun}/{$nomorUrut}";
    }

    /**
     * Generate nama file output
     */
    private static function generateFileName(Pengajuan $pengajuan, string $jenisDokumen): string
    {
        $jenisDokumenLabel = match($jenisDokumen) {
            'rekomendasi_keswan' => 'rekomendasi_keswan',
            'skkh' => 'skkh',
            'surat_keterangan_veteriner' => 'skv',
            'izin_pengeluaran' => 'izin_pengeluaran',
            'izin_pemasukan' => 'izin_pemasukan',
            default => 'dokumen',
        };

        $timestamp = now()->format('YmdHis');
        return "{$jenisDokumenLabel}_{$pengajuan->id}_{$timestamp}.docx";
    }

    /**
     * Download dokumen yang sudah di-generate
     */
    public static function downloadDokumen(DokumenPengajuan $dokumen)
    {
        $filePath = storage_path('app/public/' . $dokumen->path_file);
        
        if (!file_exists($filePath)) {
            throw new \Exception("File dokumen tidak ditemukan.");
        }

        return response()->download($filePath, $dokumen->nama_file);
    }
}

