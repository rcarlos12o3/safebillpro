<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantCleanEstablishmentIdForSpecialCustomer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // El cliente especial "Clientes - Varios" (99999999) debe estar disponible
        // en todas las tiendas, por lo tanto su establishment_id debe ser NULL
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
        // No revertir - el cliente especial debe permanecer sin establishment_id
    }
}
