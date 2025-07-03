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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('bidang_usaha', [
                'hewan_ternak',
                'hewan_kesayangan', 
                'produk_hewan_produk_olahan',
                'gabungan_di_antaranya'
            ])->nullable()->after('jenis_akun');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('bidang_usaha');
        });
    }
};
