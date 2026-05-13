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
        Schema::create('incoming_barangs', function (Blueprint $table) {
    $table->id();
    $table->date('tgl_masuk');
    $table->foreignId('barang_id')->constrained('barangs')->cascadeOnDelete();
    $table->integer('qty');
    $table->decimal('harga', 15, 2);
    $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incoming_barangs');
    }
};
