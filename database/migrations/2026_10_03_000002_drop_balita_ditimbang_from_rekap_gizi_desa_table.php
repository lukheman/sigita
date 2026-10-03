<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rekap_gizi_desa', function (Blueprint $table) {
            $table->dropColumn(['jumlah_balita', 'jumlah_ditimbang']);
        });
    }

    public function down(): void
    {
        Schema::table('rekap_gizi_desa', function (Blueprint $table) {
            $table->integer('jumlah_balita')->default(0)->after('periode');
            $table->integer('jumlah_ditimbang')->default(0)->after('jumlah_balita');
        });
    }
};
