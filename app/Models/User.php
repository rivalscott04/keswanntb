<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Lab404\Impersonate\Models\Impersonate;

class User extends Authenticatable implements FilamentUser
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

    /**
     * Determine if the user can access the Filament admin panel.
     * 
     * This method is required by FilamentUser contract to prevent
     * "forbidden" errors on shared hosting like Plesk.
     * 
     * @param Panel $panel
     * @return bool
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Allow access if user is admin
        if ($this->is_admin ?? false) {
            return true;
        }

        // Allow access if user has wewenang (authority)
        // Check both relationship and foreign key for safety
        if (($this->relationLoaded('wewenang') && $this->wewenang) || 
            ($this->wewenang_id ?? null)) {
            return true;
        }

        // Deny access by default
        return false;
    }
}
