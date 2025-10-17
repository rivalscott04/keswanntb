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
        // Ambil semua kab/kota di pulau Lombok berdasarkan nama
        $kabKotaLombok = KabKota::whereIn('nama', [
            'Kota Mataram',
            'Kab. Lombok Barat', 
            'Kab. Lombok Tengah',
            'Kab. Lombok Timur',
            'Kab. Lombok Utara'
        ])->pluck('id');

        if ($kabKotaLombok->isEmpty()) {
            return 0;
        }

        // Ambil kuota total untuk semua kab/kota di Lombok
        $kuotaTotal = Kuota::where('tahun', $tahun)
            ->where('jenis_ternak_id', $jenisTernakId)
            ->whereIn('kab_kota_id', $kabKotaLombok)
            ->where('jenis_kelamin', $jenisKelamin)
            ->where('jenis_kuota', $jenisKuota)
            ->where('pulau', 'Lombok')
            ->sum('kuota');

        // Hitung kuota yang sudah digunakan
        $kuotaDigunakan = self::where('tahun', $tahun)
            ->where('jenis_ternak_id', $jenisTernakId)
            ->whereIn('kab_kota_id', $kabKotaLombok)
            ->where('jenis_kelamin', $jenisKelamin)
            ->where('jenis_penggunaan', $jenisKuota)
            ->where('pulau', 'Lombok')
            ->sum('jumlah_digunakan');

        return max(0, $kuotaTotal - $kuotaDigunakan);
    }
}