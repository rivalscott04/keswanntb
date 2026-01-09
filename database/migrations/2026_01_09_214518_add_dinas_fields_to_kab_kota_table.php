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
        Schema::table('kab_kota', function (Blueprint $table) {
            $table->string('nama_dinas')->nullable()->after('nama');
            $table->string('alamat_dinas')->nullable()->after('nama_dinas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kab_kota', function (Blueprint $table) {
            $table->dropColumn(['nama_dinas', 'alamat_dinas']);
        });
    }
};
