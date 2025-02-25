<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantUpdateEstablecimientoIdInHotelFloorsAndCategoriesAndRatesAndRooms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('hotel_floors')
            ->whereNull('establishment_id')
            ->update(['establishment_id' => 1]);

        DB::table('hotel_categories')
            ->whereNull('establishment_id')
            ->update(['establishment_id' => 1]);
        
        DB::table('hotel_rates')
            ->whereNull('establishment_id')
            ->update(['establishment_id' => 1]);
        
        DB::table('hotel_rooms')
            ->whereNull('establishment_id')
            ->update(['establishment_id' => 1]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('hotel_floors')
            ->where('establishment_id', 1)
            ->update(['establishment_id' => null]);

        DB::table('hotel_categories')
            ->where('establishment_id', 1)
            ->update(['establishment_id' => null]);
        
        DB::table('hotel_rates')
            ->where('establishment_id', 1)
            ->update(['establishment_id' => null]);
        
        DB::table('hotel_rooms')
            ->where('establishment_id', 1)
            ->update(['establishment_id' => null]);
    }
}
