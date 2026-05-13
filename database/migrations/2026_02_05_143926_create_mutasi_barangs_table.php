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
        Schema::create('mutasi_barangs', function (Blueprint $table) {
    $table->id();
    $table->date('tgl_mutasi');
    $table->foreignId('barang_id')->constrained('barangs')->onDelete('cascade');
    $table->integer('qty');
    $table->enum('tipe', ['IN','OUT']);
    $table->string('keterangan')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutasi_barangs');
    }
};
