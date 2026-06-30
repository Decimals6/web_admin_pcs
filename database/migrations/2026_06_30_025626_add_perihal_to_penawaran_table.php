<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penawaran', function (Blueprint $table) {
            $table->string('perihal')->nullable()->after('no_penawaran');
            $table->string('up')->nullable()->after('customer_id'); // nama PIC customer (cth: "Ibu Lucia")
        });
    }

    public function down(): void
    {
        Schema::table('penawaran', function (Blueprint $table) {
            $table->dropColumn(['perihal', 'up']);
        });
    }
};