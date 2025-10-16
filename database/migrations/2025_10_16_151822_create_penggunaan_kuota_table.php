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
        Schema::create('penggunaan_kuota', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuan')->cascadeOnDelete();
            $table->foreignId('kuota_id')->constrained('kuota')->cascadeOnDelete();
            $table->integer('jumlah_digunakan');
            $table->enum('jenis_penggunaan', ['pemasukan', 'pengeluaran']);
            $table->foreignId('kab_kota_id')->constrained('kab_kota')->cascadeOnDelete();
            $table->integer('tahun');
            $table->foreignId('jenis_ternak_id')->constrained('jenis_ternak')->cascadeOnDelete();
            $table->string('jenis_kelamin');
            $table->string('pulau')->nullable();
            $table->timestamps();
            
            // Index untuk performa query
            $table->index(['tahun', 'jenis_ternak_id', 'kab_kota_id', 'jenis_kelamin', 'jenis_penggunaan'], 'idx_penggunaan_kuota_query');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penggunaan_kuota');
    }
};