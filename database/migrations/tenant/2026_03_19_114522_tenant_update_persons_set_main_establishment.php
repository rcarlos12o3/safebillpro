<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantUpdatePersonsSetMainEstablishment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Asignar la tienda principal (primera tienda) a todos los registros existentes sin establishment_id
        // EXCEPTO el cliente especial "Clientes - Varios" (99999999) que debe ser compartido entre todas las tiendas
        $mainEstablishmentId = DB::table('establishments')->min('id');

        if ($mainEstablishmentId) {
            DB::table('persons')
                ->whereNull('establishment_id')
                ->where('number', '!=', '99999999')
                ->update(['establishment_id' => $mainEstablishmentId]);
        }

        // Asegurar que el cliente especial 99999999 NO tenga establishment_id
        DB::table('persons')
            ->where('number', '99999999')
            ->update(['establishment_id' => null]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No revertir la asignación de establishment_id
        // Los datos quedarán como están
    }
}
