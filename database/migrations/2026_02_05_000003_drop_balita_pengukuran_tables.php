<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            Schema::table('hasil_cluster', function (Blueprint $table) {
                $table->dropForeign(['pengukuran_id']);
                $table->dropColumn('pengukuran_id');
            });
            Schema::dropIfExists('pengukuran');
            Schema::dropIfExists('balita');

            return;
        }

        // SQLite: rebuild hasil_cluster tanpa kolom pengukuran_id,
        // lalu drop tabel lama. PRAGMA foreign_keys dimatikan sementara.
        DB::statement('PRAGMA foreign_keys = OFF');

        Schema::create('hasil_cluster_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_analisis_id')->constrained('periode_analisis')->onDelete('cascade');
            $table->foreignId('rekap_gizi_desa_id')->nullable()->constrained('rekap_gizi_desa')->onDelete('cascade');
            $table->integer('cluster');
            $table->string('kategori')->nullable();
            $table->float('jarak_centroid')->nullable();
            $table->float('skor_risiko')->nullable();
            $table->timestamps();
        });

        // Salin data lama (abaikan pengukuran_id & kategori lama tetap dibawa)
        $rows = DB::table('hasil_cluster')->get();
        foreach ($rows as $row) {
            DB::table('hasil_cluster_new')->insert([
                'id' => $row->id,
                'periode_analisis_id' => $row->periode_analisis_id,
                'rekap_gizi_desa_id' => $row->rekap_gizi_desa_id ?? null,
                'cluster' => $row->cluster,
                'kategori' => $row->kategori ?? null,
                'jarak_centroid' => $row->jarak_centroid ?? null,
                'skor_risiko' => $row->skor_risiko ?? null,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        Schema::dropIfExists('hasil_cluster');
        Schema::rename('hasil_cluster_new', 'hasil_cluster');

        Schema::dropIfExists('pengukuran');
        Schema::dropIfExists('balita');

        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down(): void
    {
        // Tidak dapat mengembalikan tabel balita/pengukuran yang di-drop.
        // Down hanya untuk kompatibilitas; tabel tidak dibuat ulang.
    }
};
