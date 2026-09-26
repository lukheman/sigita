<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('periode_analisis', function (Blueprint $table) {
            $table->json('data_normalisasi')->nullable()->after('data_snapshot');
            $table->json('data_minmax')->nullable()->after('data_normalisasi');
            $table->json('data_centroid_normalized')->nullable()->after('data_minmax');
        });
    }

    public function down(): void
    {
        Schema::table('periode_analisis', function (Blueprint $table) {
            $table->dropColumn(['data_normalisasi', 'data_minmax', 'data_centroid_normalized']);
        });
    }
};
