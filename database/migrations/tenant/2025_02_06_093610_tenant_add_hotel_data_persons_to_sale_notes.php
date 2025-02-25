<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddHotelDataPersonsToSaleNotes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sale_notes', function (Blueprint $table) {
            $table->json('hotel_data_persons')->nullable()->comment('datos de personas - utilizado en hotel');
            $table->string('source_module')->nullable()->comment('origen del documento - hotel');
            $table->unsignedInteger('hotel_rent_id')->nullable()->comment('referencia donde se genera el documento - hotel');
            $table->foreign('hotel_rent_id')->references('id')->on('hotel_rents');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sale_notes', function (Blueprint $table) {
            $table->dropColumn('hotel_data_persons');
            $table->dropColumn('source_module');
            $table->dropColumn('hotel_rent_id');
        });
    }
}
