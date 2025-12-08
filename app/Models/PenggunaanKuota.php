<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenggunaanKuota extends Model
{
    use HasFactory;

    protected $table = 'penggunaan_kuota';

    protected $fillable = [
        'pengajuan_id',
        'kuota_id',
        'jumlah_digunakan',
        'jenis_penggunaan',
        'kab_kota_id',
        'tahun',
        'jenis_ternak_id',
        'jenis_kelamin',
        'pulau',
    ];

    protected $casts = [
        'jumlah_digunakan' => 'integer',
        'tahun' => 'integer',
    ];

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(Pengajuan::class);
    }

    public function kuota(): BelongsTo
    {
        return $this->belongsTo(Kuota::class);
    }

    public function kabKota(): BelongsTo
    {
        return $this->belongsTo(KabKota::class);
    }

    public function jenisTernak(): BelongsTo
    {
        return $this->belongsTo(JenisTernak::class);
    }

    /**
     * Hitung kuota tersisa untuk parameter tertentu
     */
    public static function getKuotaTersisa($tahun, $jenisTernakId, $kabKotaId, $jenisKelamin, $jenisKuota, $pulau = null)
    {
        // Ambil kuota total
        $kuotaTotal = Kuota::where('tahun', $tahun)
            ->where('jenis_ternak_id', $jenisTernakId)
            ->where('kab_kota_id', $kabKotaId)
            ->where('jenis_kelamin', $jenisKelamin)
            ->where('jenis_kuota', $jenisKuota)
            ->when($pulau, function ($query, $pulau) {
                return $query->where('pulau', $pulau);
            })
            ->value('kuota') ?? 0;

        // Hitung kuota yang sudah digunakan
        $kuotaDigunakan = self::where('tahun', $tahun)
            ->where('jenis_ternak_id', $jenisTernakId)
            ->where('kab_kota_id', $kabKotaId)
            ->where('jenis_kelamin', $jenisKelamin)
            ->where('jenis_penggunaan', $jenisKuota)
            ->when($pulau, function ($query, $pulau) {
                return $query->where('pulau', $pulau);
            })
            ->sum('jumlah_digunakan');

        return max(0, $kuotaTotal - $kuotaDigunakan);
    }

    /**
     * Hitung kuota tersisa untuk pulau Lombok (semua kab/kota di Lombok)
     */
    public static function getKuotaTersisaLombok($tahun, $jenisTernakId, $jenisKelamin, $jenisKuota)
    {
        // Ambil kuota total untuk pulau Lombok (kab_kota_id = null, pulau = 'Lombok')
        $kuotaTotal = Kuota::where('tahun', $tahun)
            ->where('jenis_ternak_id', $jenisTernakId)
            ->where('kab_kota_id', null)  // Untuk pulau Lombok, kab_kota_id adalah null
            ->where('jenis_kelamin', $jenisKelamin)
            ->where('jenis_kuota', $jenisKuota)
            ->where('pulau', 'Lombok')
            ->sum('kuota');

        // Hitung kuota yang sudah digunakan untuk pulau Lombok
        $kuotaDigunakan = self::where('tahun', $tahun)
            ->where('jenis_ternak_id', $jenisTernakId)
            ->where('jenis_kelamin', $jenisKelamin)
            ->where('jenis_penggunaan', $jenisKuota)
            ->where('pulau', 'Lombok')
            ->sum('jumlah_digunakan');

        return max(0, $kuotaTotal - $kuotaDigunakan);
    }

    /**
     * Cek apakah kombinasi jenis ternak, jenis pengajuan, dan lokasi memerlukan kuota
     * 
     * Yang memerlukan kuota:
     * 1. Pengeluaran sapi pedaging
     * 2. Pengeluaran kerbau pedaging
     * 3. Pengeluaran sapi bibit
     * 4. Pemasukan sapi pedaging ke pulau Lombok
     * 5. Pemasukan sapi eksotik
     * 
     * @param int|null $jenisTernakId ID jenis ternak
     * @param string $jenisPengajuan Jenis pengajuan: 'pemasukan', 'pengeluaran', atau 'antar_kab_kota'
     * @param int|null $kabKotaTujuanId ID kab/kota tujuan (untuk pemasukan)
     * @return bool True jika memerlukan kuota, false jika tidak
     */
    public static function isKuotaRequired($jenisTernakId, $jenisPengajuan, $kabKotaTujuanId = null)
    {
        if (!$jenisTernakId) {
            return false;
        }

        $jenisTernak = JenisTernak::find($jenisTernakId);
        if (!$jenisTernak) {
            return false;
        }

        $namaJenisTernak = $jenisTernak->nama;
        $namaLower = strtolower($namaJenisTernak);

        // Untuk pengeluaran
        if ($jenisPengajuan === 'pengeluaran') {
            // Pengeluaran sapi pedaging (nama mengandung "sapi" tapi bukan bibit)
            if (str_contains($namaLower, 'sapi') && !str_contains($namaLower, 'bibit')) {
                return true;
            }
            // Pengeluaran kerbau pedaging
            if (str_contains($namaLower, 'kerbau')) {
                return true;
            }
            // Pengeluaran sapi bibit
            if (str_contains($namaLower, 'bibit sapi')) {
                return true;
            }
            return false;
        }

        // Untuk pemasukan
        if ($jenisPengajuan === 'pemasukan') {
            // Pemasukan sapi pedaging ke pulau Lombok (cocokkan nama yang mengandung "sapi" kecuali bibit)
            if (str_contains($namaLower, 'sapi') && !str_contains($namaLower, 'bibit') && $kabKotaTujuanId) {
                $kabKotaTujuan = KabKota::find($kabKotaTujuanId);
                if ($kabKotaTujuan) {
                    $kabKotaLombok = [
                        'Kota Mataram',
                        'Kab. Lombok Barat',
                        'Kab. Lombok Tengah',
                        'Kab. Lombok Timur',
                        'Kab. Lombok Utara'
                    ];
                    if (in_array($kabKotaTujuan->nama, $kabKotaLombok)) {
                        return true;
                    }
                }
            }
            // Pemasukan sapi eksotik
            if (str_contains($namaLower, 'sapi eksotik')) {
                return true;
            }
            return false;
        }

        // Untuk antar kab/kota, cek berdasarkan pengeluaran dari asal
        if ($jenisPengajuan === 'antar_kab_kota') {
            // Gunakan logika pengeluaran (hanya cek dari asal)
            return self::isKuotaRequired($jenisTernakId, 'pengeluaran');
        }

        return false;
    }
}