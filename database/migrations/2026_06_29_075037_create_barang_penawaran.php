<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang_penawaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penawaran_id')->constrained('penawaran')->cascadeOnDelete();
            $table->foreignId('barang_id')->constrained('barangs')->restrictOnDelete();
            $table->string('nama_snapshot');        // nama barang saat penawaran dibuat
            $table->enum('tipe', ['consumable', 'equipment']);
            $table->string('satuan', 20);           // pcs, roll, unit, dll
            $table->unsignedSmallInteger('urutan')->default(1);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang_penawaran');
    }
};