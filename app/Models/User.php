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
    protected $fillable = [
        'id',
        'is_pernah_daftar',
        'no_sp3',
        'no_register',
        'sp3',
        'jenis_akun',
        'name',
        'email',
        'nik',
        'email_verified_at',
        'akta_pendirian',
        'surat_domisili',
        'surat_izin_usaha',
        'no_surat_izin_usaha',
        'nama_perusahaan',
        'no_npwp',
        'telepon',
        'npwp',
        'surat_tanda_daftar',
        'no_surat_tanda_daftar',
        'rekomendasi_keswan',
        'surat_kandang_penampungan',
        'dokumen_pendukung',
        'surat_permohonan_perusahaan',
        'status',
        'tanggal_verifikasi',
        'tanggal_berlaku',
        'password',
        'alamat',
        'desa',
    ];

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
}
