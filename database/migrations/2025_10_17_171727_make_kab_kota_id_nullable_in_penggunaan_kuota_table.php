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
        Schema::table('penggunaan_kuota', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['kab_kota_id']);
            
            // Make kab_kota_id nullable
            $table->unsignedBigInteger('kab_kota_id')->nullable()->change();
            
            // Re-add foreign key constraint with nullable
            $table->foreign('kab_kota_id')->references('id')->on('kab_kota')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penggunaan_kuota', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['kab_kota_id']);
            
            // Make kab_kota_id not nullable again
            $table->unsignedBigInteger('kab_kota_id')->nullable(false)->change();
            
            // Re-add foreign key constraint
            $table->foreign('kab_kota_id')->references('id')->on('kab_kota')->cascadeOnDelete();
        });
    }
};
