<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantQrApiConfiguration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("configurations", function(Blueprint $table){
            $table->string("qr_api_url", 100)->nullable();
            $table->string("qr_api_apiKey", 120)->nullable();
            $table->boolean("qr_api_enable")->default(false);
        });
        //
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('configurations', function(Blueprint $table) {
            $table->dropColumn('qr_api_url');
            $table->dropColumn('qr_api_apiKey');
            $table->dropColumn('qr_api_enable');
        });
    }
}
