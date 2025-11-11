<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kuota extends Model
{
    use HasFactory;

    protected $table = 'kuota';

    protected $fillable = [
        'jenis_ternak_id',
        'kab_kota_id',
        'tahun',
        'kuota',
        'jenis_kuota',
        'jenis_kelamin',
        'pulau',
    ];

    public function jenisTernak(): BelongsTo
    {
        return $this->belongsTo(JenisTernak::class);
    }

    public function kabKota(): BelongsTo
    {
        return $this->belongsTo(KabKota::class);
    }

    /**
     * Get the display name for kab/kota or pulau
     */
    public function getLokasiDisplayAttribute(): string
    {
        if ($this->kab_kota_id) {
            return $this->kabKota->nama;
        }
        
        if ($this->pulau) {
            return "Pulau {$this->pulau}";
        }
        
        return 'Tidak Diketahui';
    }

    /**
     * Get the used quota for this kuota record
     */
    public function getKuotaTerpakaiAttribute(): int
    {
        // Jika pulau Lombok dan kab_kota_id null: global (untuk pengeluaran)
        if ($this->pulau === 'Lombok' && $this->kab_kota_id === null) {
            // Untuk pulau Lombok global, gunakan method getKuotaTersisaLombok
            $kuotaTersisa = \App\Models\PenggunaanKuota::getKuotaTersisaLombok(
                $this->tahun,
                $this->jenis_ternak_id,
                $this->jenis_kelamin,
                $this->jenis_kuota
            );
            return max(0, $this->kuota - $kuotaTersisa);
        } else {
            // Untuk kab/kota spesifik (termasuk pemasukan ke Lombok per kab/kota), gunakan method getKuotaTersisa
            $kuotaTersisa = \App\Models\PenggunaanKuota::getKuotaTersisa(
                $this->tahun,
                $this->jenis_ternak_id,
                $this->kab_kota_id,
                $this->jenis_kelamin,
                $this->jenis_kuota,
                $this->pulau
            );
            return max(0, $this->kuota - $kuotaTersisa);
        }
    }

    /**
     * Get the remaining quota
     */
    public function getKuotaSisaAttribute(): int
    {
        return $this->kuota - $this->getKuotaTerpakaiAttribute();
    }
}
