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
        Schema::create('kuota', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_ternak_id')->constrained('jenis_ternak')->cascadeOnDelete();
            $table->foreignId('kab_kota_id')->constrained('kab_kota')->cascadeOnDelete();
            $table->integer('tahun');
            $table->enum('jenis_kuota', ['pemasukan', 'pengeluaran']);
            $table->string('jenis_kelamin');
            $table->string('pulau');
            $table->integer('kuota');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kuota');
    }
};