<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddIndexesToItemMovement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_movement', function (Blueprint $table) {

            $table->index('item_id');
            $table->index('quantity');
            $table->index('date_of_movement');
            $table->index('countable');

            $table->index('establishment_id');
            $table->index('contract_item_id');
            $table->index('devolution_item_id');
            $table->index('dispatch_item_id');

            $table->index('document_item_id');
            $table->index('expense_item_id');
            $table->index('fixed_asset_item_id');
            $table->index('fixed_asset_purchase_item_id');

            $table->index('order_form_item_id');
            $table->index('order_note_item_id');
            $table->index('purchase_item_id');
            $table->index('purchase_order_item_id');

            $table->index('purchase_quotation_item_id');
            $table->index('quotation_item_id');
            $table->index('sale_note_item_id');
            $table->index('sale_opportunity_item_id');
            $table->index('technical_service_item_id');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_movement', function (Blueprint $table) {

            $table->dropIndex(['item_id']);
            $table->dropIndex(['quantity']);
            $table->dropIndex(['date_of_movement']);
            $table->dropIndex(['countable']);

            $table->dropIndex(['establishment_id']);
            $table->dropIndex(['contract_item_id']);
            $table->dropIndex(['devolution_item_id']);
            $table->dropIndex(['dispatch_item_id']);

            $table->dropIndex(['document_item_id']);
            $table->dropIndex(['expense_item_id']);
            $table->dropIndex(['fixed_asset_item_id']);
            $table->dropIndex(['fixed_asset_purchase_item_id']);

            $table->dropIndex(['order_form_item_id']);
            $table->dropIndex(['order_note_item_id']);
            $table->dropIndex(['purchase_item_id']);
            $table->dropIndex(['purchase_order_item_id']);

            $table->dropIndex(['purchase_quotation_item_id']);
            $table->dropIndex(['quotation_item_id']);
            $table->dropIndex(['sale_note_item_id']);
            $table->dropIndex(['sale_opportunity_item_id']);
            $table->dropIndex(['technical_service_item_id']);

        });
    }
}
