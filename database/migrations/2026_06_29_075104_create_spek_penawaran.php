<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spek_penawaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_penawaran_id')->constrained('barang_penawaran')->cascadeOnDelete();
            $table->string('nama_spek', 100);       // "bahan", "isi", "core", "memory", dll
            $table->string('keterangan');            // "semicoated", "1000pcs", "1 inch", dll
            $table->unsignedSmallInteger('urutan')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spek_penawaran');
    }
};