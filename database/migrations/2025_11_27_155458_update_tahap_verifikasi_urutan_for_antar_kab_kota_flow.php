<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Swap urutan: Tujuan becomes 2, Asal becomes 3
        // Current state: Asal=2, Tujuan=3
        // Target state: Tujuan=2, Asal=3
        
        // Use temporary value to avoid unique constraint issues
        DB::table('tahap_verifikasi')
            ->where('nama', 'Disnak Kab/Kota NTB Tujuan')
            ->update(['urutan' => 99]);
        
        DB::table('tahap_verifikasi')
            ->where('nama', 'Disnak Kab/Kota NTB Asal')
            ->update(['urutan' => 3]);
        
        DB::table('tahap_verifikasi')
            ->where('nama', 'Disnak Kab/Kota NTB Tujuan')
            ->update(['urutan' => 2]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert: Asal becomes 2, Tujuan becomes 3
        DB::table('tahap_verifikasi')
            ->where('nama', 'Disnak Kab/Kota NTB Asal')
            ->update(['urutan' => 2]);
        
        DB::table('tahap_verifikasi')
            ->where('nama', 'Disnak Kab/Kota NTB Tujuan')
            ->update(['urutan' => 3]);
    }
};
