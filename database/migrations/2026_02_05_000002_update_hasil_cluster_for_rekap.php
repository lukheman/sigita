<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('hasil_cluster', function (Blueprint $table) {
            $table->foreignId('rekap_gizi_desa_id')
                ->nullable()
                ->after('periode_analisis_id')
                ->constrained('rekap_gizi_desa')
                ->onDelete('cascade');
            $table->float('jarak_centroid')->nullable()->after('cluster');
            $table->float('skor_risiko')->nullable()->after('jarak_centroid');
        });

        // pengukuran_id dibuat nullable untuk masa transisi (akan di-drop di migrasi berikutnya)
        // SQLite tidak mendukung modify column, jadi ditangani via logika aplikasi.
        // Pada MySQL, jalankan: ALTER TABLE hasil_cluster MODIFY pengukuran_id BIGINT NULL
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE `hasil_cluster` MODIFY `pengukuran_id` BIGINT UNSIGNED NULL');
        }

        Schema::table('periode_analisis', function (Blueprint $table) {
            $table->string('periode_data', 7)->nullable()->after('judul');
            $table->json('data_snapshot')->nullable()->after('data_centroid');
        });
    }

    public function down(): void
    {
        Schema::table('periode_analisis', function (Blueprint $table) {
            $table->dropColumn(['periode_data', 'data_snapshot']);
        });

        Schema::table('hasil_cluster', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rekap_gizi_desa_id');
            $table->dropColumn(['jarak_centroid', 'skor_risiko']);
        });
    }
};
