<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDocumentGroupIdToBulkUploadTemp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('bulk_upload_temp')) {
            Schema::table('bulk_upload_temp', function (Blueprint $table) {
                if (!Schema::hasColumn('bulk_upload_temp', 'document_group_id')) {
                    $table->string('document_group_id', 100)->nullable()->after('batch_id');
                    $table->index('document_group_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('bulk_upload_temp')) {
            Schema::table('bulk_upload_temp', function (Blueprint $table) {
                if (Schema::hasColumn('bulk_upload_temp', 'document_group_id')) {
                    $table->dropIndex(['document_group_id']);
                    $table->dropColumn('document_group_id');
                }
            });
        }
    }
}
