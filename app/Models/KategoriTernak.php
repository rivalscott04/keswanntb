<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriTernak extends Model
{
    use HasFactory;

    protected $table = 'kategori_ternak';

    protected $fillable = [
        'nama',
    ];

    public function jenisTernaks(): HasMany
    {
        return $this->hasMany(JenisTernak::class);
    }
}
