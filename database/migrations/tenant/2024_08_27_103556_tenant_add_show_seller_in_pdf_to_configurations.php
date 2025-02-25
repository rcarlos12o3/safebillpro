<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddShowSellerInPdfToConfigurations extends Migration
{
    public function up()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->boolean('show_seller_in_pdf')->default(1);
            $table->boolean('show_bank_accounts_in_pdf')->default(1);
        });
    }

    public function down()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('show_seller_in_pdf');
            $table->dropColumn('show_bank_accounts_in_pdf');
        });
    }
}
