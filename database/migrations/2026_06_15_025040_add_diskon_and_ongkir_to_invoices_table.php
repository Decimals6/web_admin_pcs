<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Menambahkan field diskon dan ongkir setelah kolom grand_total
            $table->decimal('diskon', 15, 2)->default(0)->after('grand_total');
            $table->decimal('ongkir', 15, 2)->default(0)->after('diskon');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Hapus kolom yang tadi ditambahkan jika melakukan rollback
            $table->dropColumn(['ongkir']);
        });
    }
};
