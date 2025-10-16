<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DokumenPengajuan extends Model
{
    use HasFactory;

    protected $table = 'dokumen_pengajuan';

    protected $fillable = [
        'pengajuan_id',
        'user_id',
        'jenis_dokumen',
        'nama_file',
        'path_file',
        'ukuran_file',
        'tipe_file',
        'keterangan',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(Pengajuan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get URL untuk download dokumen
     */
    public function getUrlDownloadAttribute(): string
    {
        return asset('storage/' . $this->path_file);
    }

    /**
     * Get nama file yang user-friendly
     */
    public function getNamaFileDisplayAttribute(): string
    {
        $extensions = [
            'rekomendasi_keswan' => 'Rekomendasi Keswan',
            'skkh' => 'SKKH',
            'surat_keterangan_veteriner' => 'Surat Keterangan Veteriner',
            'izin_pengeluaran' => 'Izin Pengeluaran',
            'izin_pemasukan' => 'Izin Pemasukan',
            'dokumen_lainnya' => 'Dokumen Lainnya',
        ];

        $jenis = $extensions[$this->jenis_dokumen] ?? $this->jenis_dokumen;
        return $jenis . ' - ' . $this->nama_file;
    }

    /**
     * Get ukuran file dalam format yang mudah dibaca
     */
    public function getUkuranFileDisplayAttribute(): string
    {
        if (!$this->ukuran_file) {
            return '-';
        }

        $bytes = (int) $this->ukuran_file;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Scope untuk dokumen aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Scope untuk jenis dokumen tertentu
     */
    public function scopeJenisDokumen($query, $jenis)
    {
        return $query->where('jenis_dokumen', $jenis);
    }

    /**
     * Get dokumen berdasarkan pengajuan dan jenis
     */
    public static function getDokumenByPengajuanDanJenis($pengajuanId, $jenisDokumen)
    {
        return self::where('pengajuan_id', $pengajuanId)
            ->where('jenis_dokumen', $jenisDokumen)
            ->aktif()
            ->first();
    }

    /**
     * Get semua dokumen untuk pengajuan
     */
    public static function getDokumenByPengajuan($pengajuanId)
    {
        return self::where('pengajuan_id', $pengajuanId)
            ->aktif()
            ->orderBy('jenis_dokumen')
            ->get();
    }
}