<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Update bidang_id untuk jenis_ternak yang belum ter-set berdasarkan kategori_ternak:
     * - Ruminansia Besar (1), Ruminansia Kecil (2), Unggas & Telur (3) -> bidang P3HP (1)
     * - Produk Ternak (4) -> bidang Kesmavet (2) 
     * - Hewan Kesayangan (5) -> bidang Keswan (3)
     */
    public function up(): void
    {
        // Update berdasarkan kategori_ternak
        // P3HP: kategori 1 (Ruminansia Besar), 2 (Ruminansia Kecil), 3 (Unggas & Telur)
        DB::table('jenis_ternak')
            ->whereIn('kategori_ternak_id', [1, 2, 3])
            ->whereNull('bidang_id')
            ->update(['bidang_id' => 1]);

        // Kesmavet: kategori 4 (Produk Ternak)
        DB::table('jenis_ternak')
            ->where('kategori_ternak_id', 4)
            ->whereNull('bidang_id')
            ->update(['bidang_id' => 2]);

        // Keswan: kategori 5 (Hewan Kesayangan)
        DB::table('jenis_ternak')
            ->where('kategori_ternak_id', 5)
            ->whereNull('bidang_id')
            ->update(['bidang_id' => 3]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu rollback karena ini data update, bukan schema change
    }
};
