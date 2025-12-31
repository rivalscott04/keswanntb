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
                'pemasukan' => public_path('docs/DRAFT PEmasukan.doc'),
                'pengeluaran' => public_path('docs/DRAFT PENGAJUAN.doc'),
                'antar_kab_kota' => public_path('docs/DRAFT PENGAJUAN.doc'),
                'default' => public_path('docs/DRAFT PENGAJUAN.doc'),
            ],
            'skkh' => [
                'default' => public_path('docs/DRAFT PENGAJUAN.doc'),
            ],
            'surat_keterangan_veteriner' => [
                'default' => public_path('docs/DRAFT PENGAJUAN.doc'),
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

        // Cek apakah file template adalah .doc/.docx atau .pdf
        $extension = strtolower(pathinfo($templatePath, PATHINFO_EXTENSION));
        
        if (!in_array($extension, ['doc', 'docx'])) {
            throw new \Exception("Template harus berupa file Word (.doc atau .docx). File yang ditemukan: {$extension}");
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

        // Format tanggal
        $tanggalSekarang = Carbon::now();
        $tanggalSurat = $pengajuan->tanggal_surat_permohonan 
            ? Carbon::parse($pengajuan->tanggal_surat_permohonan) 
            : $tanggalSekarang;

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
            'tanggal_surat_permohonan' => $tanggalSurat->translatedFormat('d F Y'),
            'tanggal_surat' => $tanggalSurat->translatedFormat('d F Y'), // Alias
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
            'jumlah_ternak' => number_format($pengajuan->jumlah_ternak, 0, ',', '.'),
            'jumlah' => number_format($pengajuan->jumlah_ternak, 0, ',', '.'), // Alias
            'jenis_kelamin' => ucfirst($pengajuan->jenis_kelamin),
            'jeniskelamin' => ucfirst($pengajuan->jenis_kelamin), // Alias tanpa underscore (sesuai template)
            'ras_ternak' => $pengajuan->ras_ternak ?? '-',
            'ras' => $pengajuan->ras_ternak ?? '-', // Alias
            'satuan' => $pengajuan->satuan ?? 'ekor',
            
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
            'jmlhari' => $pengajuan->tanggal_surat_permohonan 
                ? $tanggalSurat->diffInDays($tanggalSekarang) 
                : '-', // Jumlah hari dari tanggal surat sampai sekarang
            
            // Tanggal dokumen
            'tanggal_dokumen' => $tanggalSekarang->translatedFormat('d F Y'),
            'tanggal' => $tanggalSekarang->translatedFormat('d F Y'), // Alias
            'tanggal_ttd' => $tanggalSekarang->translatedFormat('d F Y'),
            'tanggal_sekarang' => $tanggalSekarang->translatedFormat('d F Y'), // Alias
            
            // Biodata kadis
            'nama_kadis' => $biodataKadis->nama ?? '-',
            'pangkat_kadis' => $biodataKadis->jabatan ?? '-',
            'jabatan_kadis' => $biodataKadis->jabatan ?? '-', // Alias
            'nip_kadis' => $biodataKadis->nip ?? '-',
            'nip' => $biodataKadis->nip ?? '-', // Alias
        ];

        // Tambahkan data khusus berdasarkan jenis dokumen
        switch ($jenisDokumen) {
            case 'rekomendasi_keswan':
                // Generate nomor dokumen rekomendasi
                $data['nomor_dokumen'] = self::generateNomorDokumen($pengajuan, 'REKOM');
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

