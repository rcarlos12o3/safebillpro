<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddQrChatToConfigurations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->string('qrchat_url')->nullable()->comment('datos de acceso para envio gratuito mediante whatsapp');
            $table->string('qrchat_app_key')->nullable()->comment('datos de acceso para envio gratuito mediante whatsapp');
            $table->string('qrchat_auth_key')->nullable()->comment('datos de acceso para envio gratuito mediante whatsapp');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('qrchat_url');
            $table->dropColumn('qrchat_app_key');
            $table->dropColumn('qrchat_auth_key');
        });
    }
}
