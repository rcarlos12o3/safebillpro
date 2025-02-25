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
            ['id'=>'50','value' => 'preventa', 'description' => 'PreVenta', 'sort' => 2],
            ['id'=>'51','value' => 'guia', 'description' => 'Guías de Remisión', 'sort' => 9],
            ['id'=>'52','value' => 'comprobante', 'description' => 'Comprobantes Pendientes', 'sort' => 10],
        ]);
        DB::table('modules')->where('value', 'dashboard')->update(['sort' => 1]);
        DB::table('modules')->where('value', 'preventa')->update(['sort' => 2]);
        DB::table('modules')->where('value', 'documents')->update(['sort' => 3]);
        DB::table('modules')->where('value', 'purchases')->update(['sort' => 4]);
        DB::table('modules')->where('value', 'persons')->update(['sort' => 5]);
        DB::table('modules')->where('value', 'items')->update(['sort' => 6]);
        DB::table('modules')->where('value', 'inventory')->update(['sort' => 7]);
        DB::table('modules')->where('value', 'finance')->update(['sort' => 8]);
        DB::table('modules')->where('value', 'guia')->update(['sort' => 9]);
        DB::table('modules')->where('value', 'comprobante')->update(['sort' => 10]);
        DB::table('modules')->where('value', 'advanced')->update(['sort' => 11]);
        DB::table('modules')->where('value', 'accounting')->update(['sort' => 12]);
        DB::table('modules')->where('value', 'reports')->update(['sort' => 13]);
        DB::table('modules')->where('value', 'ecommerce')->update(['sort' => 14]);
        DB::table('modules')->where('value', 'restaurant_app')->update(['sort' => 15]);
        DB::table('modules')->where('value', 'digemid')->update(['sort' => 16]);
        DB::table('modules')->where('value', 'hotels')->update(['sort' => 17]);
        DB::table('modules')->where('value', 'full_suscription_app')->update(['sort' => 18]);
        DB::table('modules')->where('value', 'suscription_app')->update(['sort' => 19]);
        DB::table('modules')->where('value', 'documentary-procedure')->update(['sort' => 20]);
        DB::table('modules')->where('value', 'production_app')->update(['sort' => 21]);
        DB::table('modules')->where('value', 'cuenta')->update(['sort' => 22]);
        DB::table('modules')->where('value', 'establishments')->update(['sort' => 23]);
        DB::table('modules')->where('value', 'apps')->update(['sort' => 24]);
        DB::table('modules')->where('value', 'configuration')->update(['sort' => 25]);
        DB::table('modules')->where('value', 'app_2_generator')->update(['sort' => 26]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        DB::table('modules')->insert([
            ['id' => 6, 'value' => 'pos', 'description' => 'Punto de venta (POS)', 'sort' => 3],
            ['id' => 25, 'value' => 'generate_link_app', 'description' => 'Generador de link de pago', 'sort' => 19],
        ]);

        DB::table('modules')->where('value', 'dashboard')->update(['sort' => 1]);
        DB::table('modules')->where('value', 'documents')->update(['sort' => 2]);
        DB::table('modules')->where('value', 'ecommerce')->update(['sort' => 4]);
        DB::table('modules')->where('value', 'items')->update(['sort' => 5]);
        DB::table('modules')->where('value', 'persons')->update(['sort' => 6]);
        DB::table('modules')->where('value', 'purchases')->update(['sort' => 7]);
        DB::table('modules')->where('value', 'inventory')->update(['sort' => 7]);
        DB::table('modules')->where('value', 'accounting')->update(['sort' => 10]);
        DB::table('modules')->where('value', 'finance')->update(['sort' => 11]);
        DB::table('modules')->where('value', 'advanced')->update(['sort' => 8]);
        DB::table('modules')->where('value', 'reports')->update(['sort' => 9]);
        DB::table('modules')->where('value', 'configuration')->update(['sort' => 12]);
        DB::table('modules')->where('value', 'apps')->update(['sort' => 1]);
        DB::table('modules')->where('value', 'suscription_app')->update(['sort' => 16]);
        DB::table('modules')->where('value', 'production_app')->update(['sort' => 17]);
        DB::table('modules')->where('value', 'restaurant_app')->update(['sort' => 18]);
        DB::table('modules')->where('value', 'full_suscription_app')->update(['sort' => 17]);
        DB::table('modules')->where('value', 'digemid')->update(['sort' => 15]);
        DB::table('modules')->where('value', 'documentary-procedure')->update(['sort' => 15]);
        DB::table('modules')->where('value', 'establishments')->update(['sort' => 7]);

    }
}
