<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPlateNumberDisplayOptionsToConfigurations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->boolean('show_plate_number_in_header')->default(true)->comment('Mostrar placa en encabezado del PDF');
            $table->boolean('show_plate_number_in_description')->default(false)->comment('Mostrar placa en descripción del producto');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn(['show_plate_number_in_header', 'show_plate_number_in_description']);
        });
    }
}
