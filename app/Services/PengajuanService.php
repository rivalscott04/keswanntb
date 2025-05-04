<?php

namespace App\Services;

use App\Models\Pengajuan;
use App\Models\TahapVerifikasi;
use App\Models\HistoriPengajuan;
use App\Models\Kuota;
use Filament\Notifications\Notification;

class PengajuanService
{
    public static function verifikasi(Pengajuan $record, $user, array $data)
    {
        $tahapBerikutnya = TahapVerifikasi::where('urutan', $record->tahapVerifikasi->urutan + 1)
            ->orderBy('urutan')
            ->first();

        // Skip tahap "Disnak Kab/Kota NTB Tujuan" jika pengeluaran
        if (
            $record->jenis_pengajuan === 'pengeluaran' &&
            $tahapBerikutnya &&
            str_contains(strtolower($tahapBerikutnya->nama), 'tujuan')
        ) {
            $tahapBerikutnya = TahapVerifikasi::where('urutan', $tahapBerikutnya->urutan + 1)
                ->orderBy('urutan')
                ->first();
        }
        // Skip tahap "Disnak Kab/Kota NTB Asal" jika pemasukan
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

        // Jika tahap saat ini Disnak Provinsi, lakukan pengurangan kuota dan update status ke 'disetujui', lalu lanjut ke DPMPTSP
        if ($record->tahapVerifikasi->nama === 'Disnak Provinsi') {
            // Kurangi kuota asal
            Kuota::where('kab_kota_id', $record->kab_kota_asal_id)
                ->where('jenis_ternak_id', $record->jenis_ternak_id)
                ->where('jenis_kelamin', $record->jenis_kelamin)
                ->where('jenis_kuota', 'keluar')
                ->decrement('kuota', $record->jumlah_ternak);
            // Kurangi kuota tujuan
            Kuota::where('kab_kota_id', $record->kab_kota_tujuan_id)
                ->where('jenis_ternak_id', $record->jenis_ternak_id)
                ->where('jenis_kelamin', $record->jenis_kelamin)
                ->where('jenis_kuota', 'masuk')
                ->decrement('kuota', $record->jumlah_ternak);
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
        $tahap = $record->jenis_pengajuan === 'pemasukan'
            ? TahapVerifikasi::where('urutan', 3)->first()
            : TahapVerifikasi::where('urutan', 2)->first();
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
            $tahapAsal = TahapVerifikasi::where('urutan', 2)->first();
            $tahapTujuan = TahapVerifikasi::where('urutan', 3)->first();
            $query->whereIn('tahap_verifikasi_id', array_filter([$tahapAsal?->id, $tahapTujuan?->id]))
                ->whereIn('status', ['menunggu', 'diproses'])
                ->where(function ($q) use ($user, $tahapAsal, $tahapTujuan) {
                    if ($tahapAsal) {
                        $q->orWhere(function ($q2) use ($user, $tahapAsal) {
                            $q2->where('tahap_verifikasi_id', $tahapAsal->id)
                                ->where('kab_kota_asal_id', $user->kab_kota_id);
                        });
                    }
                    if ($tahapTujuan) {
                        $q->orWhere(function ($q2) use ($user, $tahapTujuan) {
                            $q2->where('tahap_verifikasi_id', $tahapTujuan->id)
                                ->where('kab_kota_tujuan_id', $user->kab_kota_id);
                        });
                    }
                });
        } elseif ($user->wewenang->nama === 'DPMPTSP') {
            $tahap = TahapVerifikasi::where('nama', 'DPMPTSP')->first();
            if ($tahap) {
                $query->where('tahap_verifikasi_id', $tahap->id)
                    ->where('status', 'disetujui');
            }
        } elseif ($user->wewenang->nama === 'biasa' || $user->wewenang->nama === 'Pengusaha') {
            $query->where('user_id', $user->id)
                ->where('status', 'ditolak');
        }
        return $query;
    }
}