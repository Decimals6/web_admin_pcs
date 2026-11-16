<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mutasi_barangs', function (Blueprint $table) {
            $table->foreignId('sampel_id')
                ->nullable()
                ->after('delivery_note_detail_id')
                ->constrained('sampels')
                ->nullOnDelete();

            $table->string('sumber', 30)
                ->default('manual')
                ->after('tipe');
        });
    }

    public function down(): void
    {
        Schema::table('mutasi_barangs', function (Blueprint $table) {
            $table->dropForeign(['sampel_id']);
            $table->dropColumn(['sampel_id', 'sumber']);
        });
    }
};
