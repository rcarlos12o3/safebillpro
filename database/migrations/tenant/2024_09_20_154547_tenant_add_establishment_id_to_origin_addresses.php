<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddEstablishmentIdToOriginAddresses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('origin_addresses', function (Blueprint $table) {
            $table->char('country_id',2)->after('is_active')->default('PE');
            $table->integer('establishment_id')->after('country_id')->nullable();
            $table->string('establishment_code', 4)->after('establishment_id')->default('0000');
            $table->foreign('country_id')->references('id')->on('countries');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('origin_addresses', function (Blueprint $table) {
            $table->dropColumn('country_id');
            $table->dropColumn('establishment_id');
            $table->dropColumn('establishment_code');
        });
    }
}
