<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddEnabledPosWaiterToRestaurantConfigurations extends Migration
{
    public function up()
    {
        Schema::table('restaurant_configurations', function (Blueprint $table) {
            $table->boolean('enabled_command_waiter')->default(false);
            $table->boolean('enabled_pos_waiter')->default(false);
        });
    }

    public function down()
    {
        Schema::table('restaurant_configurations', function (Blueprint $table) {
            $table->dropColumn('enabled_command_waiter');
            $table->dropColumn('enabled_pos_waiter');
        });
    }
}
