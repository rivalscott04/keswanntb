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
        Schema::create('jenis_ternak', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_ternak_id')->constrained('kategori_ternak')->cascadeOnDelete();
            $table->foreignId('bidang_id')->constrained('bidang')->cascadeOnDelete();
            $table->string('nama');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_ternak');
    }
};
