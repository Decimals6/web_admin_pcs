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
        Schema::table('sewa_kendaraan', function (Blueprint $table) {
            $table->string('nopol')->after('user_id'); // Biar nopol ada di setelah user_id
        });
    }

    public function down(): void
    {
        Schema::table('sewa_kendaraan', function (Blueprint $table) {
            $table->dropColumn('nopol');
        });
    }
};
