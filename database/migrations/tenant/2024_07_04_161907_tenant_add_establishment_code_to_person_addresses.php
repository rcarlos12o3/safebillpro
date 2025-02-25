<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddEstablishmentCodeToPersonAddresses extends Migration
{
    public function up()
    {
        Schema::table('person_addresses', function (Blueprint $table) {
            $table->string('establishment_code', 4)->default('0000');
        });
    }

    public function down()
    {
        Schema::table('person_addresses', function (Blueprint $table) {
            $table->dropColumn('establishment_code');
        });
    }
}
