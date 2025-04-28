<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_pernah_daftar')->default(false);
            $table->string('no_sp3')->nullable();
            $table->string('no_register')->nullable();
            $table->string('sp3')->nullable();
            $table->string('jenis_akun')->nullable();
            $table->string('nik')->nullable();
            $table->string('akta_pendirian')->nullable();
            $table->string('surat_domisili')->nullable();
            $table->string('surat_izin_usaha')->nullable();
            $table->string('no_surat_izin_usaha')->nullable();
            $table->string('nama_perusahaan')->nullable();
            $table->string('no_npwp')->nullable();
            $table->string('telepon')->nullable();
            $table->string('npwp')->nullable();
            $table->string('surat_tanda_daftar')->nullable();
            $table->string('no_surat_tanda_daftar')->nullable();
            $table->string('rekomendasi_keswan')->nullable();
            $table->string('surat_kandang_penampungan')->nullable();
            $table->string('dokumen_pendukung')->nullable();
            $table->string('surat_permohonan_perusahaan')->nullable();
            $table->tinyInteger('status')->nullable()->default(0);
            $table->dateTime('tanggal_verifikasi')->nullable();
            $table->dateTime('tanggal_berlaku')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_pernah_daftar',
                'no_sp3',
                'no_register',
                'sp3',
                'jenis_akun',
                'nik',
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
                'tanggal_berlaku'
            ]);
        });
    }
};
