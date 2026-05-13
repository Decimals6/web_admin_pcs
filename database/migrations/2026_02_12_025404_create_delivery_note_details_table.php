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
        Schema::create('delivery_note_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('delivery_note_id')
                ->constrained('delivery_notes')
                ->cascadeOnDelete();

            $table->foreignId('order_detail_id')
                ->constrained('order_details')
                ->cascadeOnDelete();

            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_note_details');
    }
};
