<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bidang extends Model
{
    use HasFactory;

    protected $table = 'bidang';

    protected $fillable = [
        'nama',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function jenisTernaks(): HasMany
    {
        return $this->hasMany(JenisTernak::class);
    }
}
