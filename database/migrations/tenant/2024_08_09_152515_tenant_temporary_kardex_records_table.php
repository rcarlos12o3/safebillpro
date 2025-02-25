<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantTemporaryKardexRecordsTable extends Migration
{
    public function up()
    {
        Schema::create('temporary_kardex_records', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('inventory_kardex_id')->nullable();
            $table->string('item_name')->nullable();
            $table->datetime('date_time')->nullable();
            $table->string('date_of_issue')->nullable();
            $table->string('number')->nullable();
            $table->string('sale_note_asoc')->nullable();
            $table->string('order_note_asoc')->nullable();
            $table->string('doc_asoc')->nullable();
            $table->string('inventory_kardexable_type')->nullable();
            $table->string('item_warehouse_price')->nullable();
            $table->string('warehouse')->nullable();
            $table->string('input')->nullable();
            $table->string('output')->nullable();
            $table->decimal('balance')->nullable();
            $table->string('type_transaction')->nullable();
            $table->string('date_of_register')->nullable();
            $table->string('guide_id')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('temporary_kardex_records');
    }
}
