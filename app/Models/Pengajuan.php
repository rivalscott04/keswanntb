<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengajuan extends Model
{
    use HasFactory;

    protected $table = 'pengajuan';

    protected $fillable = [
        'user_id',
        'jenis_ternak_id',
        'provinsi_id',
        'kab_kota_id',
        'tahap_verifikasi_id',
        'nomor_pengajuan',
        'nama_pemohon',
        'alamat',
        'no_hp',
        'email',
        'jumlah_ternak',
        'keterangan',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jenisTernak(): BelongsTo
    {
        return $this->belongsTo(JenisTernak::class);
    }

    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(Provinsi::class);
    }

    public function kabKota(): BelongsTo
    {
        return $this->belongsTo(KabKota::class);
    }

    public function tahapVerifikasi(): BelongsTo
    {
        return $this->belongsTo(TahapVerifikasi::class);
    }

    public function kabKotaAsal()
    {
        return $this->belongsTo(KabKota::class, 'kab_kota_asal_id');
    }

    public function kabKotaTujuan()
    {
        return $this->belongsTo(KabKota::class, 'kab_kota_tujuan_id');
    }

    public function provinsiAsal()
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_asal_id');
    }

    public function provinsiTujuan()
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_tujuan_id');
    }
}
