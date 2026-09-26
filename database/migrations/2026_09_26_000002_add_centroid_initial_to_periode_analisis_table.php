<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('periode_analisis', function (Blueprint $table) {
            $table->json('data_centroid_initial')->nullable()->after('data_centroid_normalized');
            $table->boolean('centroid_manual')->default(false)->after('data_centroid_initial');
        });
    }

    public function down(): void
    {
        Schema::table('periode_analisis', function (Blueprint $table) {
            $table->dropColumn(['data_centroid_initial', 'centroid_manual']);
        });
    }
};
