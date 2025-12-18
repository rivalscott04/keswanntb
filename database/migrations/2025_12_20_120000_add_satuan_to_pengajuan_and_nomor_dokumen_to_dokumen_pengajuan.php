<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSatuanToPengajuanAndNomorDokumenToDokumenPengajuan extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pengajuan', function (Blueprint $table) {
            // Satuan untuk jumlah komoditas (ekor, butir, kg, liter, dll)
            $table->string('satuan', 50)->default('ekor')->after('jumlah_ternak');
        });

        Schema::table('dokumen_pengajuan', function (Blueprint $table) {
            // Nomor dokumen resmi (opsional)
            $table->string('nomor_dokumen')->nullable()->after('nama_file');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan', function (Blueprint $table) {
            $table->dropColumn('satuan');
        });

        Schema::table('dokumen_pengajuan', function (Blueprint $table) {
            $table->dropColumn('nomor_dokumen');
        });
    }
}

