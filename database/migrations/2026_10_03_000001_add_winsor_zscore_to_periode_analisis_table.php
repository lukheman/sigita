<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('periode_analisis', function (Blueprint $table) {
            $table->json('data_winsor_bounds')->nullable()->after('data_centroid_initial');
            $table->json('data_mean_std')->nullable()->after('data_winsor_bounds');
            $table->json('data_winsorized')->nullable()->after('data_mean_std');
            $table->json('data_wcss')->nullable()->after('data_winsorized');
            $table->json('data_iterations')->nullable()->after('data_wcss');
        });
    }

    public function down(): void
    {
        Schema::table('periode_analisis', function (Blueprint $table) {
            $table->dropColumn(['data_winsor_bounds', 'data_mean_std', 'data_winsorized', 'data_wcss', 'data_iterations']);
        });
    }
};
