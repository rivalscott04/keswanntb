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
        Schema::create('sp3', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('jenis_akun', ['perusahaan', 'perorangan'])->default('perusahaan');
            
            // Data Perusahaan
            $table->string('nama_perusahaan')->nullable();
            $table->enum('bidang_usaha', [
                'hewan_ternak',
                'hewan_kesayangan', 
                'produk_hewan_produk_olahan',
                'gabungan_di_antaranya'
            ])->nullable();
            $table->string('akta_pendirian')->nullable(); // file path
            $table->string('surat_domisili')->nullable(); // file path
            $table->string('nib')->nullable(); // file path
            $table->string('no_nib')->nullable();
            $table->string('npwp')->nullable(); // file path
            $table->string('no_npwp')->nullable();
            $table->string('rekomendasi_keswan')->nullable(); // file path
            $table->string('surat_kandang_penampungan')->nullable(); // file path
            $table->string('surat_permohonan_perusahaan')->nullable(); // file path
            $table->string('dokumen_pendukung')->nullable(); // file path
            
            // Status dan Nomor
            $table->string('no_sp3')->nullable();
            $table->string('no_register')->nullable();
            $table->boolean('is_pernah_daftar')->default(false);
            $table->string('sp3')->nullable(); // file path
            
            // Status Verifikasi
            $table->enum('status', [
                'draft',
                'submitted',
                'verified_kabupaten',
                'verified_provinsi',
                'approved',
                'rejected'
            ])->default('draft');
            
            $table->timestamp('verified_kabupaten_at')->nullable();
            $table->foreignId('verified_kabupaten_by')->nullable()->constrained('users');
            $table->timestamp('verified_provinsi_at')->nullable();
            $table->foreignId('verified_provinsi_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            
            $table->timestamp('tanggal_verifikasi')->nullable();
            $table->timestamp('tanggal_berlaku')->nullable();
            
            $table->text('catatan_kabupaten')->nullable();
            $table->text('catatan_provinsi')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sp3');
    }
};
