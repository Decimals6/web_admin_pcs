<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('incoming_barangs', function (Blueprint $table) {
        $table->string('no_sj')->nullable()->after('supplier_id');
        $table->string('no_invoice')->nullable()->after('no_sj');
        $table->unsignedBigInteger('order_id')->nullable()->after('no_invoice');

        $table->foreign('order_id')
              ->references('id')
              ->on('orders')
              ->nullOnDelete();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incoming_barangs', function (Blueprint $table) {
            //
        });
    }
};
