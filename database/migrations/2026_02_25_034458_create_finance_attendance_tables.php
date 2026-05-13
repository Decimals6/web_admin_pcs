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
        // 3. Tabel Master Sewa Kendaraan
        Schema::create('sewa_kendaraan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('nominal', 15, 2); // Nominal sewa per hari
            $table->timestamps();
        });

        // 4. Tabel Premi Hadir (Hasil Kalkulasi Akhir)
        Schema::create('premi_hadir', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('absensi_id')->constrained('absensi')->onDelete('cascade');
            $table->integer('total_hadir');
            $table->decimal('nominal_per_hadir', 15, 2);
            $table->decimal('total_premi', 15, 2); // (Total Hadir * Nominal) + (Total Hadir * Sewa Kendaraan)
            $table->string('status')->default('pending'); // pending, paid, dll
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_attendance_tables');
    }
};
