<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantAddEstablishmentIdToPersons extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->unsignedInteger('establishment_id')->nullable()->after('id');
        });

        // Asignar la tienda principal (primera tienda) a todos los registros existentes
        // EXCEPTO el cliente especial "Clientes - Varios" (99999999) que debe ser compartido
        $mainEstablishmentId = DB::table('establishments')->min('id');

        if ($mainEstablishmentId) {
            DB::table('persons')
                ->whereNull('establishment_id')
                ->where('number', '!=', '99999999')
                ->update(['establishment_id' => $mainEstablishmentId]);
        }

        Schema::table('persons', function (Blueprint $table) {
            $table->foreign('establishment_id')->references('id')->on('establishments');
            $table->index('establishment_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropForeign(['establishment_id']);
            $table->dropIndex(['establishment_id']);
            $table->dropColumn('establishment_id');
        });
    }
}
