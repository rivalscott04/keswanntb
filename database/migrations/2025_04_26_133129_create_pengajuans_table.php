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
        Schema::create('pengajuans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('jenis_ternak_id')->constrained()->onDelete('cascade');
            $table->foreignId('provinsi_id')->constrained()->onDelete('cascade');
            $table->foreignId('kab_kota_id')->constrained()->onDelete('cascade');
            $table->foreignId('tahap_verifikasi_id')->constrained()->onDelete('cascade');
            $table->string('nomor_pengajuan')->unique();
            $table->string('nama_pemohon');
            $table->string('alamat');
            $table->string('no_hp');
            $table->string('email');
            $table->integer('jumlah_ternak');
            $table->text('keterangan')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuans');
    }
};
