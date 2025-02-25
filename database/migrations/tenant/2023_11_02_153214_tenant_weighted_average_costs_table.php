<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantWeightedAverageCostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
 
    public function up()
    {
        Schema::create('weighted_average_costs', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date_of_issue')->index();
            $table->integer('origin_id');
            $table->string('origin_type');
            $table->unsignedInteger('item_id');
            $table->decimal('quantity', 12, 4);
            $table->decimal('cost', 16, 6);
            $table->decimal('total', 12, 2);
            $table->decimal('weighted_cost', 12, 2);
            $table->decimal('stock', 12, 4);
            $table->timestamps();

            $table->index(['origin_id','origin_type'], 'origin_index');
            $table->foreign('item_id')->references('id')->on('items');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('weighted_average_costs');
    }

}
