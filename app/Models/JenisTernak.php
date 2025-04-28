<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisTernak extends Model
{
    use HasFactory;

    protected $table = 'jenis_ternak';

    protected $fillable = [
        'kategori_ternak_id',
        'bidang_id',
        'nama',
    ];

    public function kategoriTernak(): BelongsTo
    {
        return $this->belongsTo(KategoriTernak::class);
    }

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(Bidang::class);
    }

    public function pengajuans(): HasMany
    {
        return $this->hasMany(Pengajuan::class);
    }

    public function kuotas(): HasMany
    {
        return $this->hasMany(Kuota::class);
    }
}
