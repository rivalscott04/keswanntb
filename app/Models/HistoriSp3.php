<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoriSp3 extends Model
{
    use HasFactory;

    protected $table = 'histori_sp3';

    protected $fillable = [
        'sp3_id',
        'user_id',
        'status',
        'catatan',
        'data_sebelum',
        'data_sesudah',
    ];

    protected $casts = [
        'data_sebelum' => 'array',
        'data_sesudah' => 'array',
    ];

    public function sp3(): BelongsTo
    {
        return $this->belongsTo(Sp3::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
