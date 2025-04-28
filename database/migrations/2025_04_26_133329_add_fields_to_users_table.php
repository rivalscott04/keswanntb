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
            $table->foreignId('kab_kota_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('wewenang_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('bidang_id')->nullable()->constrained()->onDelete('set null');
            $table->string('no_hp')->nullable();
            $table->text('alamat')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['kab_kota_id']);
            $table->dropForeign(['wewenang_id']);
            $table->dropForeign(['bidang_id']);
            $table->dropColumn(['kab_kota_id', 'wewenang_id', 'bidang_id', 'no_hp', 'alamat']);
        });
    }
};
