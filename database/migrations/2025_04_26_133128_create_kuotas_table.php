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
        Schema::create('kuotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_ternak_id')->constrained()->onDelete('cascade');
            $table->foreignId('kab_kota_id')->constrained()->onDelete('cascade');
            $table->integer('tahun');
            $table->integer('kuota');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kuotas');
    }
};
