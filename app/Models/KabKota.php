<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KabKota extends Model
{
    use HasFactory;

    protected $table = 'kab_kota';

    protected $fillable = [
        'provinsi_id',
        'nama',
    ];

    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(Provinsi::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
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
