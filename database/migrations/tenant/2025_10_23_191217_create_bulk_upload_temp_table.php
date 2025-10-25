<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateBulkUploadTempTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Verificar si la tabla ya existe antes de crearla
        if (!Schema::hasTable('bulk_upload_temp')) {
            Schema::create('bulk_upload_temp', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id'); // Usuario que subió
                $table->string('type', 20)->default('documents'); // Tipo: documents, customers, items
                $table->string('batch_id', 50); // ID único del lote/upload
                $table->date('date_of_issue')->nullable(); // Fecha de emisión seleccionada

                // Datos del Excel
                $table->json('row_data'); // Datos completos de la fila

                // Validaciones
                $table->boolean('is_valid')->default(false);
                $table->text('validation_errors')->nullable();

                // Estado
                $table->enum('status', ['pending', 'processed', 'error'])->default('pending');
                $table->unsignedInteger('document_id')->nullable(); // ID del documento creado
                $table->text('process_error')->nullable(); // Error al procesar

                $table->timestamps();

                // Índices
                $table->index('batch_id');
                $table->index('user_id');
                $table->index('status');
            });
        }

        // Agregar permiso de carga masiva a module_levels (si no existe)
        $exists = DB::table('module_levels')->where('value', 'bulk_upload')->exists();

        if (!$exists) {
            // Obtener el ID del módulo de Ventas (documents)
            $moduleId = DB::table('modules')->where('value', 'documents')->value('id');

            if ($moduleId) {
                DB::table('module_levels')->insert([
                    'value' => 'bulk_upload',
                    'description' => 'Carga Masiva',
                    'module_id' => $moduleId,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Eliminar permiso de module_levels
        DB::table('module_levels')->where('value', 'bulk_upload')->delete();

        Schema::dropIfExists('bulk_upload_temp');
    }
}
