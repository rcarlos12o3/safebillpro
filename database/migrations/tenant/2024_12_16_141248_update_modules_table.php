<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('modules')->insert([
            ['id'=>'50','value' => 'preventa', 'description' => 'PreVenta', 'order_menu' => 2],
            ['id'=>'51','value' => 'guia', 'description' => 'Guías de Remisión', 'order_menu' => 9],
            ['id'=>'52','value' => 'comprobante', 'description' => 'Comprobantes Pendientes', 'order_menu' => 10],
        ]);
        DB::table('modules')->where('value', 'dashboard')->update(['order_menu' => 1]);
        DB::table('modules')->where('value', 'preventa')->update(['order_menu' => 2]);
        DB::table('modules')->where('value', 'documents')->update(['order_menu' => 3]);
        DB::table('modules')->where('value', 'purchases')->update(['order_menu' => 4]);
        DB::table('modules')->where('value', 'persons')->update(['order_menu' => 5]);
        DB::table('modules')->where('value', 'items')->update(['order_menu' => 6]);
        DB::table('modules')->where('value', 'inventory')->update(['order_menu' => 7]);
        DB::table('modules')->where('value', 'finance')->update(['order_menu' => 8]);
        DB::table('modules')->where('value', 'guia')->update(['order_menu' => 9]);
        DB::table('modules')->where('value', 'comprobante')->update(['order_menu' => 10]);
        DB::table('modules')->where('value', 'advanced')->update(['order_menu' => 11]);
        DB::table('modules')->where('value', 'accounting')->update(['order_menu' => 12]);
        DB::table('modules')->where('value', 'reports')->update(['order_menu' => 13]);
        DB::table('modules')->where('value', 'ecommerce')->update(['order_menu' => 14]);
        DB::table('modules')->where('value', 'restaurant_app')->update(['order_menu' => 15]);
        DB::table('modules')->where('value', 'digemid')->update(['order_menu' => 16]);
        DB::table('modules')->where('value', 'hotels')->update(['order_menu' => 17]);
        DB::table('modules')->where('value', 'full_suscription_app')->update(['order_menu' => 18]);
        DB::table('modules')->where('value', 'suscription_app')->update(['order_menu' => 19]);
        DB::table('modules')->where('value', 'documentary-procedure')->update(['order_menu' => 20]);
        DB::table('modules')->where('value', 'production_app')->update(['order_menu' => 21]);
        DB::table('modules')->where('value', 'cuenta')->update(['order_menu' => 22]);
        DB::table('modules')->where('value', 'establishments')->update(['order_menu' => 23]);
        DB::table('modules')->where('value', 'apps')->update(['order_menu' => 24]);
        DB::table('modules')->where('value', 'configuration')->update(['order_menu' => 25]);
        DB::table('modules')->where('value', 'app_2_generator')->update(['order_menu' => 26]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        DB::table('modules')->insert([
            ['id' => 6, 'value' => 'pos', 'description' => 'Punto de venta (POS)', 'order_menu' => 3],
            ['id' => 25, 'value' => 'generate_link_app', 'description' => 'Generador de link de pago', 'order_menu' => 19],
        ]);

        DB::table('modules')->where('value', 'dashboard')->update(['order_menu' => 1]);
        DB::table('modules')->where('value', 'documents')->update(['order_menu' => 2]);
        DB::table('modules')->where('value', 'ecommerce')->update(['order_menu' => 4]);
        DB::table('modules')->where('value', 'items')->update(['order_menu' => 5]);
        DB::table('modules')->where('value', 'persons')->update(['order_menu' => 6]);
        DB::table('modules')->where('value', 'purchases')->update(['order_menu' => 7]);
        DB::table('modules')->where('value', 'inventory')->update(['order_menu' => 7]);
        DB::table('modules')->where('value', 'accounting')->update(['order_menu' => 10]);
        DB::table('modules')->where('value', 'finance')->update(['order_menu' => 11]);
        DB::table('modules')->where('value', 'advanced')->update(['order_menu' => 8]);
        DB::table('modules')->where('value', 'reports')->update(['order_menu' => 9]);
        DB::table('modules')->where('value', 'configuration')->update(['order_menu' => 12]);
        DB::table('modules')->where('value', 'apps')->update(['order_menu' => 1]);
        DB::table('modules')->where('value', 'suscription_app')->update(['order_menu' => 16]);
        DB::table('modules')->where('value', 'production_app')->update(['order_menu' => 17]);
        DB::table('modules')->where('value', 'restaurant_app')->update(['order_menu' => 18]);
        DB::table('modules')->where('value', 'full_suscription_app')->update(['order_menu' => 17]);
        DB::table('modules')->where('value', 'digemid')->update(['order_menu' => 15]);
        DB::table('modules')->where('value', 'documentary-procedure')->update(['order_menu' => 15]);
        DB::table('modules')->where('value', 'establishments')->update(['order_menu' => 7]);

    }
}
