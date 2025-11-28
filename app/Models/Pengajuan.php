<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pengajuan extends Model
{
    use HasFactory;

    protected $table = 'pengajuan';

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jenisTernak(): BelongsTo
    {
        return $this->belongsTo(JenisTernak::class);
    }

    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(Provinsi::class);
    }

    public function kabKota(): BelongsTo
    {
        return $this->belongsTo(KabKota::class);
    }

    public function tahapVerifikasi(): BelongsTo
    {
        return $this->belongsTo(TahapVerifikasi::class);
    }

    public function kabKotaAsal()
    {
        return $this->belongsTo(KabKota::class, 'kab_kota_asal_id');
    }

    public function kabKotaTujuan()
    {
        return $this->belongsTo(KabKota::class, 'kab_kota_tujuan_id');
    }

    public function provinsiAsal()
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_asal_id');
    }

    public function provinsiTujuan()
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_tujuan_id');
    }

    public function kategoriTernak()
    {
        return $this->belongsTo(KategoriTernak::class);
    }

    /**
     * Cek apakah pengajuan ini untuk Bibit Sapi
     */
    public function isBibitSapi(): bool
    {
        return $this->jenisTernak && $this->jenisTernak->nama === 'Bibit Sapi';
    }

    /**
     * Cek apakah pengajuan ini memiliki multiple jenis kelamin (jantan dan betina)
     */
    public function hasMultipleJenisKelamin(): bool
    {
        if (!$this->isBibitSapi()) {
            return false;
        }
        
        $jumlahJantan = (int)($this->jumlah_jantan ?? 0);
        $jumlahBetina = (int)($this->jumlah_betina ?? 0);
        
        return $jumlahJantan > 0 && $jumlahBetina > 0;
    }

    public function historiPengajuan(): HasMany
    {
        return $this->hasMany(HistoriPengajuan::class);
    }

    public function penggunaanKuota(): HasMany
    {
        return $this->hasMany(PenggunaanKuota::class);
    }

    public function dokumenPengajuan(): HasMany
    {
        return $this->hasMany(DokumenPengajuan::class);
    }

    public function getKuotaTersediaAttribute()
    {
        // Cek apakah kab/kota asal atau tujuan ada di pulau Lombok
        $kabKotaAsal = $this->kabKotaAsal;
        $kabKotaTujuan = $this->kabKotaTujuan;
        
        // Daftar kab/kota di pulau Lombok
        $kabKotaLombok = [
            'Kota Mataram',
            'Kab. Lombok Barat', 
            'Kab. Lombok Tengah',
            'Kab. Lombok Timur',
            'Kab. Lombok Utara'
        ];
        
        $isLombokAsal = $kabKotaAsal && in_array($kabKotaAsal->nama, $kabKotaLombok);
        $isLombokTujuan = $kabKotaTujuan && in_array($kabKotaTujuan->nama, $kabKotaLombok);

        if ($this->jenis_pengajuan === 'antar_kab_kota') {
            // Untuk pengajuan antar kab/kota, hanya cek kuota pengeluaran dari asal
            if ($isLombokAsal) {
                return \App\Models\PenggunaanKuota::getKuotaTersisaLombok(
                    $this->tahun_pengajuan,
                    $this->jenis_ternak_id,
                    $this->jenis_kelamin,
                    'pengeluaran'
                );
            } else {
                return \App\Models\PenggunaanKuota::getKuotaTersisa(
                    $this->tahun_pengajuan,
                    $this->jenis_ternak_id,
                    $this->kab_kota_asal_id,
                    $this->jenis_kelamin,
                    'pengeluaran'
                );
            }
        } else {
            // Untuk pengajuan pemasukan/pengeluaran
            if ($this->jenis_pengajuan === 'pemasukan') {
                // Untuk pengajuan pemasukan, cek kuota pemasukan ke tujuan
                // Kuota pemasukan ke Lombok: per kab/kota (spesifik)
                if ($isLombokTujuan) {
                    // Pemasukan ke Lombok: per kab/kota
                    $kuotaPemasukan = \App\Models\PenggunaanKuota::getKuotaTersisa(
                        $this->tahun_pengajuan,
                        $this->jenis_ternak_id,
                        $this->kab_kota_tujuan_id,
                        $this->jenis_kelamin,
                        'pemasukan',
                        'Lombok'
                    );
                } else {
                    // Pemasukan ke kab/kota lain: per kab/kota
                    $kuotaPemasukan = \App\Models\PenggunaanKuota::getKuotaTersisa(
                        $this->tahun_pengajuan,
                        $this->jenis_ternak_id,
                        $this->kab_kota_tujuan_id,
                        $this->jenis_kelamin,
                        'pemasukan'
                    );
                }
                return $kuotaPemasukan;
            } else {
                // Untuk pengajuan pengeluaran, hanya cek kuota pengeluaran dari asal
                // Kuota pengeluaran dari Lombok: global
                if ($isLombokAsal) {
                    // Pengeluaran dari Lombok: global
                    return \App\Models\PenggunaanKuota::getKuotaTersisaLombok(
                        $this->tahun_pengajuan,
                        $this->jenis_ternak_id,
                        $this->jenis_kelamin,
                        'pengeluaran'
                    );
                } else {
                    // Pengeluaran dari kab/kota lain: per kab/kota
                    return \App\Models\PenggunaanKuota::getKuotaTersisa(
                        $this->tahun_pengajuan,
                        $this->jenis_ternak_id,
                        $this->kab_kota_asal_id,
                        $this->jenis_kelamin,
                        'pengeluaran'
                    );
                }
            }
        }
    }

    public function getIsKuotaPenuhAttribute()
    {
        return $this->jumlah_ternak > $this->kuota_tersedia;
    }

    public function getIsLombokAttribute()
    {
        // Daftar kab/kota di pulau Lombok
        $kabKotaLombok = [
            'Kota Mataram',
            'Kab. Lombok Barat', 
            'Kab. Lombok Tengah',
            'Kab. Lombok Timur',
            'Kab. Lombok Utara'
        ];
        
        $kabKotaAsal = $this->kabKotaAsal;
        $kabKotaTujuan = $this->kabKotaTujuan;
        
        $isLombokAsal = $kabKotaAsal && in_array($kabKotaAsal->nama, $kabKotaLombok);
        $isLombokTujuan = $kabKotaTujuan && in_array($kabKotaTujuan->nama, $kabKotaLombok);
        
        return $isLombokAsal || $isLombokTujuan;
    }

    public function canVerifyBy($user)
    {
        if ($user->is_admin) {
            return in_array($this->status, ['menunggu', 'diproses']) ||
                ($this->tahapVerifikasi->urutan === 5 && $this->status === 'disetujui');
        }
        if ($this->tahapVerifikasi->urutan === 2) {
            // Disnak Kab/Kota Tujuan (urutan 2)
            if ($this->jenis_pengajuan === 'pengeluaran') {
                // Pengeluaran skip tahap tujuan
                return false;
            }
            if ($this->jenis_pengajuan === 'antar_kab_kota') {
                // Untuk antar kab/kota, tujuan tidak perlu cek kuota
                return in_array($this->status, ['menunggu', 'diproses']) &&
                    $user->wewenang->nama === 'Disnak Kab/Kota' &&
                    $user->kab_kota_id === $this->kab_kota_tujuan_id;
            }
            // Untuk pemasukan
            return in_array($this->status, ['menunggu', 'diproses']) &&
                !$this->is_kuota_penuh &&
                $user->wewenang->nama === 'Disnak Kab/Kota' &&
                $user->kab_kota_id === $this->kab_kota_tujuan_id;
        }
        if ($this->tahapVerifikasi->urutan === 3) {
            // Disnak Kab/Kota Asal (urutan 3)
            if ($this->jenis_pengajuan === 'pemasukan') {
                // Pemasukan skip tahap asal
                return false;
            }
            if ($this->jenis_pengajuan === 'antar_kab_kota') {
                // Untuk antar kab/kota, asal tidak perlu cek kuota
                return in_array($this->status, ['menunggu', 'diproses']) &&
                    $user->wewenang->nama === 'Disnak Kab/Kota' &&
                    $user->kab_kota_id === $this->kab_kota_asal_id;
            }
            // Untuk pengeluaran
            return in_array($this->status, ['menunggu', 'diproses']) &&
                !$this->is_kuota_penuh &&
                $user->wewenang->nama === 'Disnak Kab/Kota' &&
                $user->kab_kota_id === $this->kab_kota_asal_id;
        }
        if ($this->tahapVerifikasi->urutan === 4) {
            // Disnak Provinsi
            if ($this->jenis_pengajuan === 'antar_kab_kota') {
                // Untuk antar kab/kota, provinsi tidak perlu cek kuota
                return in_array($this->status, ['menunggu', 'diproses']) &&
                    $user->wewenang->nama === 'Disnak Provinsi';
            }
            return in_array($this->status, ['menunggu', 'diproses']) &&
                !$this->is_kuota_penuh &&
                $user->wewenang->nama === 'Disnak Provinsi';
        }
        if ($this->tahapVerifikasi->urutan === 5) {
            // DPMPTSP
            return $this->status === 'disetujui' &&
                $user->wewenang->nama === 'DPMPTSP';
        }
        return false;
    }

    public function canRejectBy($user)
    {
        $user = auth()->user();
        // Admin bisa menolak di tahap yang sedang berjalan
        if ($user->is_admin) {
            return in_array($this->status, ['menunggu', 'diproses']);
        }
        // DPMPTSP tidak bisa menolak
        if ($this->tahapVerifikasi->urutan === 5) {
            return false;
        }
        if ($this->tahapVerifikasi->urutan === 2) {
            // Disnak Kab/Kota Tujuan (urutan 2)
            if ($this->jenis_pengajuan === 'pengeluaran') {
                // Pengeluaran skip tahap tujuan
                return false;
            }
            return in_array($this->status, ['menunggu', 'diproses']) &&
                $user->wewenang->nama === 'Disnak Kab/Kota' &&
                $user->kab_kota_id === $this->kab_kota_tujuan_id;
        }
        if ($this->tahapVerifikasi->urutan === 3) {
            // Disnak Kab/Kota Asal (urutan 3)
            if ($this->jenis_pengajuan === 'pemasukan') {
                // Pemasukan skip tahap asal
                return false;
            }
            return in_array($this->status, ['menunggu', 'diproses']) &&
                $user->wewenang->nama === 'Disnak Kab/Kota' &&
                $user->kab_kota_id === $this->kab_kota_asal_id;
        }
        if ($this->tahapVerifikasi->urutan === 4) {
            // Disnak Provinsi
            return in_array($this->status, ['menunggu', 'diproses']) &&
                $user->wewenang->nama === 'Disnak Provinsi';
        }
        return false;
    }


    public function getStatusProsesLabelAttribute()
    {
        if (in_array($this->status, ['menunggu', 'diproses'])) {
            $urutanBerikutnya = optional($this->tahapVerifikasi)->urutan;
            $tahapBerikutnya = \App\Models\TahapVerifikasi::where('urutan', $urutanBerikutnya)->first();
            // Jika pengeluaran dan tahap berikutnya adalah tujuan, skip label ke tahap setelahnya
            if ($this->jenis_pengajuan === 'pengeluaran' && $tahapBerikutnya && str_contains(strtolower($tahapBerikutnya->nama), 'tujuan')) {
                $tahapBerikutnya = \App\Models\TahapVerifikasi::where('urutan', $tahapBerikutnya->urutan + 1)->first();
            }
            return $tahapBerikutnya
                ? 'Menunggu verifikasi oleh ' . $tahapBerikutnya->nama
                : 'Menunggu verifikasi akhir / Disetujui';
        } elseif ($this->status === 'ditolak') {
            return 'Ditolak oleh ' . ($this->tahapVerifikasi->nama ?? '-');
        } elseif ($this->status === 'disetujui') {
            return 'Disetujui';
        } elseif ($this->status === 'selesai') {
            return 'Selesai';
        }
        return '-';
    }

    public function getStatusProsesColorAttribute()
    {
        return match ($this->status) {
            'menunggu' => 'gray',
            'disetujui' => 'success',
            'ditolak' => 'danger',
            'diproses' => 'warning',
            'selesai' => 'success',
            default => 'gray',
        };
    }
}
