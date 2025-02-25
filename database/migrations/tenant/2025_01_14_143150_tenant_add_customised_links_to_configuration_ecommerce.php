<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddCustomisedLinksToConfigurationEcommerce extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('configuration_ecommerce', function (Blueprint $table) {
            $table->text('title_one_customised_link')->nullable()->after('token_private_culqui');
            $table->text('title_two_customised_link')->nullable()->after('title_one_customised_link');
            $table->text('title_three_customised_link')->nullable()->after('title_two_customised_link');
            $table->text('customised_link_one')->nullable()->after('title_three_customised_link');
            $table->text('customised_link_two')->nullable()->after('customised_link_one');
            $table->text('customised_link_three')->nullable()->after('customised_link_two');
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
            $table->dropColumn('title_one_customised_link');
            $table->dropColumn('title_two_customised_link');
            $table->dropColumn('title_three_customised_link');
            $table->dropColumn('customised_link_one');
            $table->dropColumn('customised_link_two');
            $table->dropColumn('customised_link_three');
        });
    }
}
