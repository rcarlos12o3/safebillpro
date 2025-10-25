<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddBulkUploadToModuleLevels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Verificar si ya existe el permiso antes de insertarlo
        $exists = DB::table('module_levels')->where('value', 'bulk_upload')->exists();

        if (!$exists) {
            DB::table('module_levels')->insert([
                'value' => 'bulk_upload',
                'description' => 'Carga Masiva',
                'module_id' => 1, // Módulo de Documentos/Ventas
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('module_levels')->where('value', 'bulk_upload')->delete();
    }
}
