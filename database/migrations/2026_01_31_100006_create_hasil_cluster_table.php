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
        Schema::create('hasil_cluster', function (Blueprint $table) {
            $table->id();

            // Relasi ke periode analisis
            $table->foreignId('periode_analisis_id')->constrained('periode_analisis')->onDelete('cascade');

            // Relasi ke data pengukuran spesifik yang dianalisis
            $table->foreignId('pengukuran_id')->constrained('pengukuran')->onDelete('cascade');

            // Hasil Output K-Means (0, 1, 2, dst)
            $table->integer('cluster');

            // Label interpretasi (misal: "Sangat Pendek", "Normal") - Diisi otomatis oleh sistem setelah mapping
            $table->string('kategori')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_cluster');
    }
};
