<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('barang_sampel', function (Blueprint $table) {
            $table->id();
            
            // Menghubungkan ke tabel sampels
            $table->foreignId('sampel_id')->constrained('sampels');
            
            // Menghubungkan ke tabel barangs
            $table->foreignId('barang_id')->constrained('barangs');
            
            // Jumlah barang yang diberikan khusus untuk item ini
            $table->integer('jumlah'); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_sampel');
    }
};