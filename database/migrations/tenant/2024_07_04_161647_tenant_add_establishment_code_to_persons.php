<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddEstablishmentCodeToPersons extends Migration
{
    public function up()
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->string('establishment_code', 4)->default('0000')->after('address');
        });
    }

    public function down()
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropColumn('establishment_code');
        });
    }
}
