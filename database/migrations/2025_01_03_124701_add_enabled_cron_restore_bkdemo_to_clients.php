<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEnabledCronRestoreBkdemoToClients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('restore_dbname_bkdemo')->nullable()->after('start_billing_cycle');
            $table->string('restore_type_bkdemo')->nullable()->after('restore_dbname_bkdemo');
            $table->boolean('enabled_cron_restore_bkdemo')->default(false)->after('restore_type_bkdemo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('restore_dbname_bkdemo');
            $table->dropColumn('restore_type_bkdemo');
            $table->dropColumn('enabled_cron_restore_bkdemo');
        });
    }
}
