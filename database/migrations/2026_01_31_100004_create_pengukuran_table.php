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
        Schema::create('pengukuran', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel 'balita'
            $table->foreignId('balita_id')->constrained('balita')->onDelete('cascade');

            $table->date('tanggal_ukur');
            $table->integer('usia_bulan'); // Usia saat pengukuran dilakukan
            $table->decimal('berat_badan', 5, 2); // Contoh: 12.50
            $table->decimal('tinggi_badan', 5, 2); // Contoh: 98.50

            // Kolom opsional untuk catatan petugas (misal: "Anak sakit saat ditimbang")
            $table->string('catatan')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengukuran');
    }
};
