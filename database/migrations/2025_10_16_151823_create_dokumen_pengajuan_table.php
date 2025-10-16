<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dokumen_pengajuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuan')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // User yang upload
            $table->enum('jenis_dokumen', [
                'rekomendasi_keswan',
                'skkh',
                'surat_keterangan_veteriner',
                'izin_pengeluaran',
                'izin_pemasukan',
                'dokumen_lainnya'
            ]);
            $table->string('nama_file');
            $table->string('path_file');
            $table->string('ukuran_file')->nullable();
            $table->string('tipe_file')->nullable();
            $table->text('keterangan')->nullable();
            $table->enum('status', ['aktif', 'tidak_aktif'])->default('aktif');
            $table->timestamps();
            
            // Index untuk performa
            $table->index(['pengajuan_id', 'jenis_dokumen'], 'idx_dokumen_pengajuan_jenis');
            $table->index(['user_id', 'status'], 'idx_dokumen_pengajuan_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumen_pengajuan');
    }
};