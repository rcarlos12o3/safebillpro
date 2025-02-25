<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddEnabledPrintsendCommandToRestaurantConfigurations extends Migration
{
    public function up()
    {
        Schema::table('restaurant_configurations', function (Blueprint $table) {
            $table->boolean('enabled_send_command')->default(false);
            $table->boolean('enabled_print_command')->default(true);
            $table->boolean('enabled_printsend_command')->default(false);
        });
    }

    public function down()
    {
        Schema::table('restaurant_configurations', function (Blueprint $table) {
            $table->dropColumn('enabled_send_command');
            $table->dropColumn('enabled_print_command');
            $table->dropColumn('enabled_printsend_command');
        });
    }
}
