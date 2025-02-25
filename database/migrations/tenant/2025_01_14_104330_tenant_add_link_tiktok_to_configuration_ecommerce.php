<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddLinkTiktokToConfigurationEcommerce extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('configuration_ecommerce', function (Blueprint $table) {
            $table->text('link_tiktok')->nullable()->after('link_twitter');
            $table->text('link_instagram')->nullable()->after('link_tiktok');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('configuration_ecommerce', function (Blueprint $table) {
            $table->dropColumn('link_tiktok');
            $table->dropColumn('link_instagram');
        });
    }
}
