<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Lab404\Impersonate\Models\Impersonate;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, Impersonate;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'kab_kota_verified_at' => 'datetime',
            'provinsi_verified_at' => 'datetime',
            'tanggal_verifikasi' => 'datetime',
            'tanggal_berlaku' => 'datetime',
        ];
    }

    public function kabKota()
    {
        return $this->belongsTo(KabKota::class);
    }

    public function wewenang()
    {
        return $this->belongsTo(Wewenang::class);
    }

    public function bidang()
    {
        return $this->belongsTo(Bidang::class);
    }

    public function pengajuans()
    {
        return $this->hasMany(Pengajuan::class);
    }

    public function kabKotaVerifiedBy()
    {
        return $this->belongsTo(User::class, 'kab_kota_verified_by');
    }

    public function provinsiVerifiedBy()
    {
        return $this->belongsTo(User::class, 'provinsi_verified_by');
    }
}
