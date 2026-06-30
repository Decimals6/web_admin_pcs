<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('harga_penawaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_penawaran_id')->constrained('barang_penawaran')->cascadeOnDelete();
            $table->unsignedInteger('min_qty');     // consumable: tier qty | equipment: isi 1
            $table->decimal('harga', 15, 2);        // harga per satuan
            $table->timestamps();

            // pastikan tidak ada duplikat tier qty dalam 1 barang penawaran
            $table->unique(['barang_penawaran_id', 'min_qty']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harga_penawaran');
    }
};