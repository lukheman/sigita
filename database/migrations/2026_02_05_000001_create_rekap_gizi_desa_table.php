<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rekap_gizi_desa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('desa_id')->constrained('desa')->onDelete('cascade');
            // Periode laporan format YYYY-MM, misal 2026-01
            $table->string('periode', 7);
            $table->integer('jumlah_balita')->default(0);
            $table->integer('jumlah_ditimbang')->default(0);
            $table->integer('jumlah_stunting')->nullable();
            $table->integer('jumlah_gizi_kurang')->nullable();
            $table->integer('jumlah_bb_kurang')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['desa_id', 'periode']);
            $table->index('periode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekap_gizi_desa');
    }
};
