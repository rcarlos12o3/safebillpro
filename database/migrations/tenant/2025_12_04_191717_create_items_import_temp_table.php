<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsImportTempTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items_import_temp', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('batch_id', 36)->index();
            $table->unsignedInteger('warehouse_id');
            $table->integer('row_number');
            $table->json('row_data');
            $table->boolean('is_valid')->default(false);
            $table->json('validation_errors')->nullable();
            $table->enum('status', ['pending', 'processed', 'error'])->default('pending')->index();
            $table->unsignedInteger('item_id')->nullable();
            $table->text('process_error')->nullable();
            $table->string('action', 20)->nullable(); // 'create' or 'update'
            $table->json('preview_data')->nullable(); // Para mostrar cambios en la previsualización
            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
            $table->foreign('item_id')->references('id')->on('items');

            // Indexes for better performance
            $table->index(['batch_id', 'status']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('items_import_temp');
    }
}