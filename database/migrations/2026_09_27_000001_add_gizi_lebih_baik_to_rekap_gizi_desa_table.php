<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rekap_gizi_desa', function (Blueprint $table) {
            $table->integer('jumlah_gizi_lebih')->nullable()->after('jumlah_bb_kurang');
            $table->integer('jumlah_gizi_baik')->nullable()->after('jumlah_gizi_lebih');
        });
    }

    public function down(): void
    {
        Schema::table('rekap_gizi_desa', function (Blueprint $table) {
            $table->dropColumn(['jumlah_gizi_lebih', 'jumlah_gizi_baik']);
        });
    }
};
