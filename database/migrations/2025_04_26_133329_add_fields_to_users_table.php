<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('kab_kota_id')->nullable()->constrained('kab_kota')->onDelete('set null');
            $table->foreignId('wewenang_id')->nullable()->constrained('wewenang')->onDelete('set null');
            $table->foreignId('bidang_id')->nullable()->constrained('bidang')->onDelete('set null');
            $table->string('no_hp')->nullable();
            $table->text('alamat')->nullable();
            $table->string('desa')->nullable();
            $table->boolean('is_admin')->default(false);
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
            $table->dropColumn(['kab_kota_id', 'wewenang_id', 'bidang_id', 'no_hp', 'alamat', 'desa', 'is_admin']);
        });
    }
};
