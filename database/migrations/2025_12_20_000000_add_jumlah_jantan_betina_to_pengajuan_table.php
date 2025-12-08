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
        Schema::table('pengajuan', function (Blueprint $table) {
            $table->integer('jumlah_jantan')->nullable()->after('jumlah_ternak');
            $table->integer('jumlah_betina')->nullable()->after('jumlah_jantan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan', function (Blueprint $table) {
            $table->dropColumn(['jumlah_jantan', 'jumlah_betina']);
        });
    }
};








