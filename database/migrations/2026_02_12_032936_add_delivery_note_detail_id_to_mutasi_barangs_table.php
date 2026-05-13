<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mutasi_barangs', function (Blueprint $table) {

            $table->foreignId('delivery_note_detail_id')
                ->nullable()
                ->after('barang_id') // sesuaikan posisi
                ->constrained('delivery_note_details')
                ->nullOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('mutasi_barangs', function (Blueprint $table) {
            $table->dropForeign(['delivery_note_detail_id']);
            $table->dropColumn('delivery_note_detail_id');
        });
    }
};

