<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sp3 extends Model
{
    use HasFactory;

    protected $table = 'sp3';

    protected $fillable = [
        'user_id',
        'jenis_akun',
        'nama_perusahaan',
        'bidang_usaha',
        'akta_pendirian',
        'surat_domisili',
        'nib',
        'no_nib',
        'npwp',
        'no_npwp',
        'rekomendasi_keswan',
        'surat_kandang_penampungan',
        'surat_permohonan_perusahaan',
        'dokumen_pendukung',
        'no_sp3',
        'no_register',
        'is_pernah_daftar',
        'sp3',
        'status',
        'verified_kabupaten_at',
        'verified_kabupaten_by',
        'verified_provinsi_at',
        'verified_provinsi_by',
        'approved_at',
        'approved_by',
        'tanggal_verifikasi',
        'tanggal_berlaku',
        'catatan_kabupaten',
        'catatan_provinsi',
    ];

    protected $casts = [
        'verified_kabupaten_at' => 'datetime',
        'verified_provinsi_at' => 'datetime',
        'approved_at' => 'datetime',
        'tanggal_verifikasi' => 'datetime',
        'tanggal_berlaku' => 'datetime',
        'is_pernah_daftar' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifiedKabupatenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_kabupaten_by');
    }

    public function verifiedProvinsiBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_provinsi_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function historiSp3(): HasMany
    {
        return $this->hasMany(HistoriSp3::class);
    }
}
