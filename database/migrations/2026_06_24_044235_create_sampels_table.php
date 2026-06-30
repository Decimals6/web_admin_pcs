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
        Schema::create('sampels', function (Blueprint $table) {
            $table->id(); 
            $table->date('tanggal'); // Tanggal pemberian sampel
            
            // Relasi ke tabel pelanggan
            $table->foreignId('customer_id')->constrained('customers');
            
            $table->text('keterangan')->nullable(); // Keterangan umum nota sampel
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sampels');
    }
};