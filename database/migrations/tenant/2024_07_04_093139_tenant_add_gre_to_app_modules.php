<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddGreToAppModules extends Migration
{
    public function up()
    {
        DB::table('app_modules')->insert([
            'id' => 14,
            'value' => 'dispatches',
            'description' => 'G.R. Remitente',
            'order_menu' => 14,
        ]);

        DB::table('app_modules')->insert([
            'id' => 15,
            'value' => 'carrier_dispatches',
            'description' => 'G.R. Transportista',
            'order_menu' => 15,
        ]);

    }

    public function down()
    {
        DB::table('app_modules')->where('id', 14)->delete();
        DB::table('app_modules')->where('id', 15)->delete();
    }
}
