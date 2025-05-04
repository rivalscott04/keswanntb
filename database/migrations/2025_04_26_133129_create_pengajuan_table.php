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
        Schema::create('pengajuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('jenis_pengajuan'); // antar_kab_kota, pengeluaran, pemasukan
            $table->foreignId('kategori_ternak_id')->constrained('kategori_ternak');
            $table->string('jenis_kelamin');

            // File uploads
            $table->string('surat_permohonan')->nullable();
            $table->string('nomor_surat_permohonan')->nullable();
            $table->date('tanggal_surat_permohonan')->nullable();
            $table->string('skkh')->nullable();
            $table->string('nomor_skkh')->nullable();
            $table->string('hasil_uji_lab')->nullable();
            $table->string('dokumen_lainnya')->nullable();
            $table->string('izin_ptsp_daerah')->nullable(); // untuk pengeluaran & pemasukan

            // Lokasi asal
            $table->foreignId('provinsi_asal_id')->nullable()->constrained('provinsi')->cascadeOnDelete();
            $table->foreignId('kab_kota_asal_id')->nullable()->constrained('kab_kota')->cascadeOnDelete();
            $table->string('kab_kota_asal')->nullable();
            $table->string('pelabuhan_asal')->nullable();

            // Lokasi tujuan
            $table->foreignId('provinsi_tujuan_id')->nullable()->constrained('provinsi')->cascadeOnDelete();
            $table->foreignId('kab_kota_tujuan_id')->nullable()->constrained('kab_kota')->cascadeOnDelete();
            $table->string('kab_kota_tujuan')->nullable();
            $table->string('pelabuhan_tujuan')->nullable();

            // Informasi ternak
            $table->foreignId('jenis_ternak_id')->constrained('jenis_ternak')->cascadeOnDelete();
            $table->integer('jumlah_ternak');
            $table->string('ras_ternak')->nullable();

            // Status dan tracking
            $table->foreignId('tahap_verifikasi_id')->constrained('tahap_verifikasi')->cascadeOnDelete();
            $table->string('status')->default('menunggu');
            $table->integer('tahun_pengajuan');
            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan');
    }
};