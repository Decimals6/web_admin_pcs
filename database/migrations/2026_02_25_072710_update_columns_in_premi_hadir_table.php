<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('premi_hadir', function (Blueprint $table) {
            // Menghapus kolom lama yang rancu
            $table->dropColumn(['nominal_per_hadir', 'total_premi']);

            // Menambah kolom rincian baru
            $table->decimal('nominal_premi_harian', 15, 2)->after('total_hadir')->default(0);
            $table->decimal('nominal_sewa_harian', 15, 2)->after('nominal_premi_harian')->default(0);
            
            $table->decimal('subtotal_premi', 15, 2)->after('nominal_sewa_harian')->default(0);
            $table->decimal('subtotal_sewa', 15, 2)->after('subtotal_premi')->default(0);
            $table->decimal('total_keseluruhan', 15, 2)->after('subtotal_sewa')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('premi_hadir', function (Blueprint $table) {
            $table->dropColumn([
                'nominal_premi_harian', 
                'nominal_sewa_harian', 
                'subtotal_premi', 
                'subtotal_sewa', 
                'total_keseluruhan'
            ]);
            $table->decimal('nominal_per_hadir', 15, 2);
            $table->decimal('total_premi', 15, 2);
        });
    }
};