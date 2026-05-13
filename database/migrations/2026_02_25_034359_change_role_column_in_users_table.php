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
        // Schema::table('users', function (Blueprint $table) {
        //     // Mengubah enum ke string (varchar)
        //     $table->string('role')->change();
        // });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Jika rollback, balikkan ke enum (sesuaikan isinya dengan yang lama)
            $table->enum('role', ['admin', 'sales', 'karyawan'])->change();
        });
    }
};
