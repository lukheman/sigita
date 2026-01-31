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
        Schema::create('periode_analisis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // Siapa yang memproses

            $table->string('judul'); // Contoh: "Analisis Stunting Januari 2026"
            $table->date('tanggal_proses');
            $table->integer('jumlah_cluster'); // Nilai K yang dipakai (misal: 3)
            $table->integer('total_data'); // Jumlah data balita yang diolah

            // Menyimpan titik pusat centroid (disimpan dalam bentuk JSON agar fleksibel)
            // Berguna untuk menampilkan grafik tanpa hitung ulang
            $table->json('data_centroid')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periode_analisis');
    }
};
