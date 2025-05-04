<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoriPengajuan extends Model
{
    use HasFactory;

    protected $table = 'histori_pengajuan';

    protected $fillable = [
        'pengajuan_id',
        'tahap_verifikasi_id',
        'user_id',
        'status',
        'alasan_penolakan',
        'catatan',
    ];

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(Pengajuan::class);
    }

    public function tahapVerifikasi(): BelongsTo
    {
        return $this->belongsTo(TahapVerifikasi::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}