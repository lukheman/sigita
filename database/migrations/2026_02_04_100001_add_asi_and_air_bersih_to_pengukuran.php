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
        Schema::table('pengukuran', function (Blueprint $table) {
            $table->boolean('asi_eksklusif')->default(false)->after('tinggi_badan');
            $table->boolean('akses_air_bersih')->default(false)->after('asi_eksklusif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengukuran', function (Blueprint $table) {
            $table->dropColumn(['asi_eksklusif', 'akses_air_bersih']);
        });
    }
};
