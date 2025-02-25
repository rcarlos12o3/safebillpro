<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRestaurantItemOrderStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('restaurant_item_order_statuses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('table_id');
            $table->integer('item_id');
            $table->json('item')->nullable();
            $table->integer('quantity');
            $table->integer('status');
            $table->string('status_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('restaurant_item_order_statuses');
    }
}
