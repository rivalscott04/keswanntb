<?php

namespace App\Services;

use App\Models\Pengajuan;
use App\Models\TahapVerifikasi;
use App\Models\HistoriPengajuan;
use App\Models\Kuota;
use App\Models\PenggunaanKuota;
use Filament\Notifications\Notification;

class PengajuanService
{
    public static function verifikasi(Pengajuan $record, $user, array $data)
    {
        $tahapBerikutnya = TahapVerifikasi::where('urutan', $record->tahapVerifikasi->urutan + 1)
            ->orderBy('urutan')
            ->first();

        // Skip tahap "Disnak Kab/Kota NTB Tujuan" (urutan 2) jika pengeluaran
        if (
            $record->jenis_pengajuan === 'pengeluaran' &&
            $tahapBerikutnya &&
            str_contains(strtolower($tahapBerikutnya->nama), 'tujuan')
        ) {
            $tahapBerikutnya = TahapVerifikasi::where('urutan', $tahapBerikutnya->urutan + 1)
                ->orderBy('urutan')
                ->first();
        }
        // Skip tahap "Disnak Kab/Kota NTB Asal" (urutan 3) jika pemasukan
        if (
            $record->jenis_pengajuan === 'pemasukan' &&
            $tahapBerikutnya &&
            str_contains(strtolower($tahapBerikutnya->nama), 'asal')
        ) {
            $tahapBerikutnya = TahapVerifikasi::where('urutan', $tahapBerikutnya->urutan + 1)
                ->orderBy('urutan')
                ->first();
        }

        HistoriPengajuan::create([
            'pengajuan_id' => $record->id,
            'tahap_verifikasi_id' => $record->tahap_verifikasi_id,
            'user_id' => $user->id,
            'status' => 'disetujui',
            'catatan' => $data['catatan'] ?? null,
        ]);

        // Jika tahap saat ini Disnak Provinsi (urutan 4), lakukan pencatatan penggunaan kuota dan update status ke 'disetujui', lalu lanjut ke DPMPTSP
        // Kuota akan berkurang ketika Disnak Provinsi menyetujui pengajuan
        if ($record->tahapVerifikasi->urutan === 4) {
            if ($record->jenis_pengajuan === 'pemasukan') {
                // Untuk pengajuan pemasukan, hanya catat kuota pemasukan ke tujuan
                self::catatPenggunaanKuota($record, 'pemasukan');
            } elseif ($record->jenis_pengajuan === 'pengeluaran') {
                // Untuk pengajuan pengeluaran, hanya catat kuota pengeluaran dari asal
                self::catatPenggunaanKuota($record, 'pengeluaran');
            } elseif ($record->jenis_pengajuan === 'antar_kab_kota') {
                // Untuk pengajuan antar kab/kota, catat kedua kuota:
                // 1. Kuota pengeluaran dari kab/kota asal
                // 2. Kuota pemasukan ke kab/kota tujuan
                self::catatPenggunaanKuota($record, 'pengeluaran');
                self::catatPenggunaanKuota($record, 'pemasukan');
            }
            
            $record->update([
                'tahap_verifikasi_id' => $tahapBerikutnya->id,
                'status' => 'disetujui',
            ]);
        } elseif ($tahapBerikutnya) {
            $record->update([
                'tahap_verifikasi_id' => $tahapBerikutnya->id,
                'status' => 'diproses',
            ]);
        } else {
            // Tahap terakhir (DPMPTSP)
            $record->update([
                'status' => 'selesai',
            ]);
        }
        Notification::make()
            ->title('Pengajuan berhasil diverifikasi')
            ->success()
            ->send();
    }


    public static function tolak(Pengajuan $record, $user, array $data)
    {
        $tahapPengusaha = TahapVerifikasi::where('urutan', 1)->first();
        HistoriPengajuan::create([
            'pengajuan_id' => $record->id,
            'tahap_verifikasi_id' => $record->tahap_verifikasi_id,
            'user_id' => $user->id,
            'status' => 'ditolak',
            'alasan_penolakan' => $data['alasan_penolakan'] ?? null,
        ]);
        $record->update([
            'status' => 'ditolak',
            'tahap_verifikasi_id' => $tahapPengusaha->id,
        ]);
        Notification::make()
            ->title('Pengajuan berhasil ditolak')
            ->success()
            ->send();
    }

    public static function ajukanKembali(Pengajuan $record, $user, array $data)
    {
        // Untuk pemasukan: mulai dari urutan 2 (Tujuan)
        // Untuk pengeluaran: mulai dari urutan 3 (Asal)
        // Untuk antar_kab_kota: mulai dari urutan 2 (Tujuan)
        $tahap = $record->jenis_pengajuan === 'pemasukan' || $record->jenis_pengajuan === 'antar_kab_kota'
            ? TahapVerifikasi::where('urutan', 2)->first()
            : TahapVerifikasi::where('urutan', 3)->first();
        $record->update([
            'status' => 'menunggu',
            'tahap_verifikasi_id' => $tahap->id,
        ]);
        HistoriPengajuan::create([
            'pengajuan_id' => $record->id,
            'tahap_verifikasi_id' => $tahap->id,
            'user_id' => $user->id,
            'status' => 'menunggu',
            'catatan' => $data['catatan'] ?? null,
        ]);
        Notification::make()
            ->title('Pengajuan berhasil diajukan kembali')
            ->success()
            ->send();
    }


    public static function countPerluDitindaklanjutiFor($user, $jenisPengajuan)
    {
        return self::queryPerluDitindaklanjutiFor($user, $jenisPengajuan)->count();
    }

    public static function queryPerluDitindaklanjutiFor($user, $jenisPengajuan)
    {
        $query = Pengajuan::query();
        if ($jenisPengajuan) {
            $query->where('jenis_pengajuan', $jenisPengajuan);
        }
        if ($user->is_admin || $user->wewenang->nama === 'Disnak Provinsi') {
            $tahap = TahapVerifikasi::where('nama', 'Disnak Provinsi')->first();
            if ($tahap) {
                $query->where('tahap_verifikasi_id', $tahap->id)
                    ->whereIn('status', ['menunggu', 'diproses']);
            }
        } elseif ($user->wewenang->nama === 'Disnak Kab/Kota') {
            // urutan 2 = Tujuan, urutan 3 = Asal
            $tahapTujuan = TahapVerifikasi::where('urutan', 2)->first();
            $tahapAsal = TahapVerifikasi::where('urutan', 3)->first();
            $query->whereIn('tahap_verifikasi_id', array_filter([$tahapTujuan?->id, $tahapAsal?->id]))
                ->whereIn('status', ['menunggu', 'diproses'])
                ->where(function ($q) use ($user, $tahapTujuan, $tahapAsal) {
                    if ($tahapTujuan) {
                        $q->orWhere(function ($q2) use ($user, $tahapTujuan) {
                            $q2->where('tahap_verifikasi_id', $tahapTujuan->id)
                                ->where('kab_kota_tujuan_id', $user->kab_kota_id);
                        });
                    }
                    if ($tahapAsal) {
                        $q->orWhere(function ($q2) use ($user, $tahapAsal) {
                            $q2->where('tahap_verifikasi_id', $tahapAsal->id)
                                ->where('kab_kota_asal_id', $user->kab_kota_id);
                        });
                    }
                });
        } elseif ($user->wewenang->nama === 'DPMPTSP') {
            $tahap = TahapVerifikasi::where('nama', 'DPMPTSP')->first();
            if ($tahap) {
                $query->where('tahap_verifikasi_id', $tahap->id)
                    ->where('status', 'disetujui');
            }
        } else {
            $query->where('user_id', $user->id)
                ->where('status', 'ditolak');
        }

        return $query;
    }

    /**
     * Catat penggunaan kuota untuk pengajuan
     * Kuota akan berkurang ketika Disnak Provinsi menyetujui pengajuan
     */
    public static function catatPenggunaanKuota(Pengajuan $pengajuan, $jenisPenggunaan)
    {
        // Cek apakah jenis ternak ini memerlukan kuota
        $kabKotaTujuanId = $jenisPenggunaan === 'pemasukan' ? $pengajuan->kab_kota_tujuan_id : null;
        if (!PenggunaanKuota::isKuotaRequired($pengajuan->jenis_ternak_id, $jenisPenggunaan === 'pemasukan' ? 'pemasukan' : 'pengeluaran', $kabKotaTujuanId)) {
            // Jika tidak perlu kuota, skip pencatatan
            return;
        }

        // Cek apakah jenis ternak adalah Bibit Sapi
        $jenisTernak = $pengajuan->jenisTernak;
        $isBibitSapi = $jenisTernak && $jenisTernak->nama === 'Bibit Sapi';

        // Untuk Bibit Sapi, catat kuota jantan dan betina terpisah jika ada
        if ($isBibitSapi) {
            $jumlahJantan = (int)($pengajuan->jumlah_jantan ?? 0);
            $jumlahBetina = (int)($pengajuan->jumlah_betina ?? 0);

            // Catat kuota untuk jantan jika ada (bisa hanya jantan saja)
            if ($jumlahJantan > 0) {
                self::catatPenggunaanKuotaPerJenisKelamin($pengajuan, $jenisPenggunaan, 'jantan', $jumlahJantan);
            }

            // Catat kuota untuk betina jika ada (bisa hanya betina saja)
            if ($jumlahBetina > 0) {
                self::catatPenggunaanKuotaPerJenisKelamin($pengajuan, $jenisPenggunaan, 'betina', $jumlahBetina);
            }

            // Return karena sudah handle untuk Bibit Sapi (baik hanya salah satu atau keduanya)
            return;
        }

        // Untuk jenis ternak lain, gunakan logika lama
        // Cek apakah penggunaan kuota untuk jenis ini sudah pernah dicatat
        $sudahDicatat = PenggunaanKuota::where('pengajuan_id', $pengajuan->id)
            ->where('jenis_penggunaan', $jenisPenggunaan)
            ->where('jenis_kelamin', $pengajuan->jenis_kelamin)
            ->exists();
        
        if ($sudahDicatat) {
            // Jika sudah pernah dicatat, skip untuk mencegah duplikasi
            return;
        }

        $kabKotaId = $jenisPenggunaan === 'pengeluaran' 
            ? $pengajuan->kab_kota_asal_id 
            : $pengajuan->kab_kota_tujuan_id;

        // Cek apakah kab/kota ada di pulau Lombok
        $kabKota = \App\Models\KabKota::find($kabKotaId);
        $kabKotaLombok = [
            'Kota Mataram',
            'Kab. Lombok Barat', 
            'Kab. Lombok Tengah',
            'Kab. Lombok Timur',
            'Kab. Lombok Utara'
        ];
        $isLombok = $kabKota && in_array($kabKota->nama, $kabKotaLombok);

        // Ambil kuota yang sesuai
        $kuota = Kuota::where('tahun', $pengajuan->tahun_pengajuan)
            ->where('jenis_ternak_id', $pengajuan->jenis_ternak_id)
            ->where('jenis_kelamin', $pengajuan->jenis_kelamin)
            ->where('jenis_kuota', $jenisPenggunaan)
            ->when($isLombok && $jenisPenggunaan === 'pengeluaran', function ($query) {
                // Untuk pengeluaran dari Lombok: global (kab_kota_id = null, pulau = 'Lombok')
                return $query->where('kab_kota_id', null)->where('pulau', 'Lombok');
            }, function ($query) use ($kabKotaId, $isLombok, $jenisPenggunaan) {
                // Untuk pemasukan ke Lombok: per kab/kota (kab_kota_id = [id], pulau = 'Lombok')
                // Untuk Sumbawa dan lainnya: per kab/kota (kab_kota_id = [id], pulau sesuai)
                $query->where('kab_kota_id', $kabKotaId);
                if ($isLombok && $jenisPenggunaan === 'pemasukan') {
                    $query->where('pulau', 'Lombok');
                }
                return $query;
            })
            ->first();

        if ($kuota) {
            PenggunaanKuota::create([
                'pengajuan_id' => $pengajuan->id,
                'kuota_id' => $kuota->id,
                'jumlah_digunakan' => $pengajuan->jumlah_ternak,
                'jenis_penggunaan' => $jenisPenggunaan,
                'kab_kota_id' => $kabKotaId,
                'tahun' => $pengajuan->tahun_pengajuan,
                'jenis_ternak_id' => $pengajuan->jenis_ternak_id,
                'jenis_kelamin' => $pengajuan->jenis_kelamin,
                'pulau' => $isLombok ? 'Lombok' : $kuota->pulau,
            ]);
        }
    }

    /**
     * Helper method untuk catat penggunaan kuota per jenis kelamin
     */
    private static function catatPenggunaanKuotaPerJenisKelamin(Pengajuan $pengajuan, $jenisPenggunaan, $jenisKelamin, $jumlah)
    {
        // Cek apakah penggunaan kuota untuk jenis ini sudah pernah dicatat
        $sudahDicatat = PenggunaanKuota::where('pengajuan_id', $pengajuan->id)
            ->where('jenis_penggunaan', $jenisPenggunaan)
            ->where('jenis_kelamin', $jenisKelamin)
            ->exists();
        
        if ($sudahDicatat) {
            // Jika sudah pernah dicatat, skip untuk mencegah duplikasi
            return;
        }

        $kabKotaId = $jenisPenggunaan === 'pengeluaran' 
            ? $pengajuan->kab_kota_asal_id 
            : $pengajuan->kab_kota_tujuan_id;

        // Cek apakah kab/kota ada di pulau Lombok
        $kabKota = \App\Models\KabKota::find($kabKotaId);
        $kabKotaLombok = [
            'Kota Mataram',
            'Kab. Lombok Barat', 
            'Kab. Lombok Tengah',
            'Kab. Lombok Timur',
            'Kab. Lombok Utara'
        ];
        $isLombok = $kabKota && in_array($kabKota->nama, $kabKotaLombok);

        // Ambil kuota yang sesuai
        $kuota = Kuota::where('tahun', $pengajuan->tahun_pengajuan)
            ->where('jenis_ternak_id', $pengajuan->jenis_ternak_id)
            ->where('jenis_kelamin', $jenisKelamin)
            ->where('jenis_kuota', $jenisPenggunaan)
            ->when($isLombok && $jenisPenggunaan === 'pengeluaran', function ($query) {
                // Untuk pengeluaran dari Lombok: global (kab_kota_id = null, pulau = 'Lombok')
                return $query->where('kab_kota_id', null)->where('pulau', 'Lombok');
            }, function ($query) use ($kabKotaId, $isLombok, $jenisPenggunaan) {
                // Untuk pemasukan ke Lombok: per kab/kota (kab_kota_id = [id], pulau = 'Lombok')
                // Untuk Sumbawa dan lainnya: per kab/kota (kab_kota_id = [id], pulau sesuai)
                $query->where('kab_kota_id', $kabKotaId);
                if ($isLombok && $jenisPenggunaan === 'pemasukan') {
                    $query->where('pulau', 'Lombok');
                }
                return $query;
            })
            ->first();

        if ($kuota) {
            PenggunaanKuota::create([
                'pengajuan_id' => $pengajuan->id,
                'kuota_id' => $kuota->id,
                'jumlah_digunakan' => $jumlah,
                'jenis_penggunaan' => $jenisPenggunaan,
                'kab_kota_id' => $kabKotaId,
                'tahun' => $pengajuan->tahun_pengajuan,
                'jenis_ternak_id' => $pengajuan->jenis_ternak_id,
                'jenis_kelamin' => $jenisKelamin,
                'pulau' => $isLombok ? 'Lombok' : $kuota->pulau,
            ]);
        }
    }

    /**
     * Hapus penggunaan kuota untuk pengajuan (jika dibatalkan)
     */
    public static function hapusPenggunaanKuota(Pengajuan $pengajuan)
    {
        PenggunaanKuota::where('pengajuan_id', $pengajuan->id)->delete();
    }
}