<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMultiUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('multi_users', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('origin_client_id')->index();
            $table->unsignedInteger('destination_client_id')->index();
            $table->unsignedInteger('origin_user_id')->index();
            $table->unsignedInteger('destination_user_id')->index();
            $table->string('email')->index();
            $table->json('user')->nullable();
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
        Schema::dropIfExists('multi_users');
    }
}
