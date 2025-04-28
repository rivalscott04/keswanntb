<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TahapVerifikasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'urutan',
    ];

    public function pengajuans(): HasMany
    {
        return $this->hasMany(Pengajuan::class);
    }
}
