<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateModulesLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::table('module_levels')->insert([
            ['id'=>'90','value' => 'dispatches', 'description' => 'G.R. Remitente', 'module_id' => 51],
            ['id'=>'91','value' => 'dispatch_carrier', 'description' => 'G.R. Transportista', 'module_id' => 51],
            ['id'=>'92','value' => 'dispatchers', 'description' => 'Transportistas', 'module_id' => 51],
            ['id'=>'93','value' => 'drivers', 'description' => 'Conductores', 'module_id' => 51],
            ['id'=>'94','value' => 'transports', 'description' => 'Vehículos', 'module_id' => 51],
        ]);
        DB::table('module_levels')->where('value', 'new_document')->update(['module_id' => 1]);
        DB::table('module_levels')->where('value', 'list_document')->update(['module_id' => 1]);
        DB::table('module_levels')->where('value', 'document_not_sent')->update(['module_id' => 52]);
        DB::table('module_levels')->where('value', 'document_contingengy')->update(['module_id' => 3]);
        DB::table('module_levels')->where('value', 'catalogs')->update(['module_id' => 1]);
        DB::table('module_levels')->where('value', 'summary_voided')->update(['module_id' => 52]);
        DB::table('module_levels')->where('value', 'quotations')->update(['module_id' => 50]);
        DB::table('module_levels')->where('value', 'sale_notes')->update(['module_id' => 1]);
        DB::table('module_levels')->where('value', 'incentives')->update(['module_id' => 1]);
        DB::table('module_levels')->where('value', 'sale-opportunity')->update(['module_id' => 50]);
        DB::table('module_levels')->where('value', 'contracts')->update(['module_id' => 50]);
        DB::table('module_levels')->where('value', 'order-note')->update(['module_id' => 50]);
        DB::table('module_levels')->where('value', 'technical-service')->update(['module_id' => 50]);
        DB::table('module_levels')->where('value', 'regularize_shipping')->update(['module_id' => 52]);
        DB::table('module_levels')->where('value', 'pos')->update(['module_id' => 1]);
        DB::table('module_levels')->where('value', 'cash')->update(['module_id' => 12]);
        DB::table('module_levels')->where('value', 'ecommerce')->update(['module_id' => 10]);
        DB::table('module_levels')->where('value', 'ecommerce_orders')->update(['module_id' => 10]);
        DB::table('module_levels')->where('value', 'ecommerce_items')->update(['module_id' => 10]);
        DB::table('module_levels')->where('value', 'ecommerce_tags')->update(['module_id' => 10]);
        DB::table('module_levels')->where('value', 'ecommerce_promotions')->update(['module_id' => 10]);
        DB::table('module_levels')->where('value', 'ecommerce_settings')->update(['module_id' => 10]);
        DB::table('module_levels')->where('value', 'items')->update(['module_id' => 17]);
        DB::table('module_levels')->where('value', 'items_packs')->update(['module_id' => 17]);
        DB::table('module_levels')->where('value', 'items_services')->update(['module_id' => 17]);
        DB::table('module_levels')->where('value', 'items_categories')->update(['module_id' => 17]);
        DB::table('module_levels')->where('value', 'items_brands')->update(['module_id' => 17]);
        DB::table('module_levels')->where('value', 'items_lots')->update(['module_id' => 17]);
        DB::table('module_levels')->where('value', 'clients')->update(['module_id' => 18]);
        DB::table('module_levels')->where('value', 'clients_types')->update(['module_id' => 18]);
        DB::table('module_levels')->where('value', 'purchases_create')->update(['module_id' => 2]);
        DB::table('module_levels')->where('value', 'purchases_list')->update(['module_id' => 2]);
        DB::table('module_levels')->where('value', 'purchases_orders')->update(['module_id' => 2]);
        DB::table('module_levels')->where('value', 'purchases_expenses')->update(['module_id' => 2]);
        DB::table('module_levels')->where('value', 'purchases_suppliers')->update(['module_id' => 2]);
        DB::table('module_levels')->where('value', 'purchases_quotations')->update(['module_id' => 2]);
        DB::table('module_levels')->where('value', 'purchases_fixed_assets_items')->update(['module_id' => 2]);
        DB::table('module_levels')->where('value', 'purchases_fixed_assets_purchases')->update(['module_id' => 2]);
        DB::table('module_levels')->where('value', 'inventory')->update(['module_id' => 8]);
        DB::table('module_levels')->where('value', 'inventory_transfers')->update(['module_id' => 8]);
        DB::table('module_levels')->where('value', 'inventory_devolutions')->update(['module_id' => 8]);
        DB::table('module_levels')->where('value', 'inventory_report_kardex')->update(['module_id' => 8]);
        DB::table('module_levels')->where('value', 'inventory_report')->update(['module_id' => 8]);
        DB::table('module_levels')->where('value', 'inventory_report_valued_kardex')->update(['module_id' => 8]);
        DB::table('module_levels')->where('value', 'users')->update(['module_id' => 14]);
        DB::table('module_levels')->where('value', 'users_establishments')->update(['module_id' => 14]);
        DB::table('module_levels')->where('value', 'advanced_retentions')->update(['module_id' => 3]);
        DB::table('module_levels')->where('value', 'advanced_perceptions')->update(['module_id' => 3]);
        DB::table('module_levels')->where('value', 'advanced_order_forms')->update(['module_id' => 3]);
        DB::table('module_levels')->where('value', 'account_report')->update(['module_id' => 9]);
        DB::table('module_levels')->where('value', 'account_formats')->update(['module_id' => 9]);
        DB::table('module_levels')->where('value', 'account_summary')->update(['module_id' => 9]);
        DB::table('module_levels')->where('value', 'finances_movements')->update(['module_id' => 12]);
        DB::table('module_levels')->where('value', 'finances_incomes')->update(['module_id' => 12]);
        DB::table('module_levels')->where('value', 'finances_unpaid')->update(['module_id' => 12]);
        DB::table('module_levels')->where('value', 'finances_to_pay')->update(['module_id' => 12]);
        DB::table('module_levels')->where('value', 'finances_payments')->update(['module_id' => 12]);
        DB::table('module_levels')->where('value', 'finances_balance')->update(['module_id' => 12]);
        DB::table('module_levels')->where('value', 'finances_payment_method_types')->update(['module_id' => 12]);
        DB::table('module_levels')->where('value', 'account_users_settings')->update(['module_id' => 11]);
        DB::table('module_levels')->where('value', 'account_users_list')->update(['module_id' => 11]);
        DB::table('module_levels')->where('value', 'hotels_reception')->update(['module_id' => 15]);
        DB::table('module_levels')->where('value', 'hotels_rates')->update(['module_id' => 15]);
        DB::table('module_levels')->where('value', 'hotels_floors')->update(['module_id' => 15]);
        DB::table('module_levels')->where('value', 'hotels_cats')->update(['module_id' => 15]);
        DB::table('module_levels')->where('value', 'hotels_rooms')->update(['module_id' => 15]);
        DB::table('module_levels')->where('value', 'documentary_offices')->update(['module_id' => 16]);
        DB::table('module_levels')->where('value', 'documentary_process')->update(['module_id' => 16]);
        DB::table('module_levels')->where('value', 'documentary_documents')->update(['module_id' => 16]);
        DB::table('module_levels')->where('value', 'documentary_actions')->update(['module_id' => 16]);
        DB::table('module_levels')->where('value', 'documentary_files')->update(['module_id' => 16]);
        DB::table('module_levels')->where('value', 'digemid')->update(['module_id' => 19]);
        DB::table('module_levels')->where('value', 'documentary_requirements')->update(['module_id' => 16]);
        DB::table('module_levels')->where('value', 'inventory_item_extra_data')->update(['module_id' => 8]);
        DB::table('module_levels')->where('value', 'configuration_company')->update(['module_id' => 5]);
        DB::table('module_levels')->where('value', 'configuration_advance')->update(['module_id' => 5]);
        DB::table('module_levels')->where('value', 'configuration_visual')->update(['module_id' => 5]);
        DB::table('module_levels')->where('value', 'advanced_purchase_settlements')->update(['module_id' => 3]);
        DB::table('module_levels')->where('value', 'suscription_app_client')->update(['module_id' => 21]);
        DB::table('module_levels')->where('value', 'suscription_app_service')->update(['module_id' => 21]);
        DB::table('module_levels')->where('value', 'suscription_app_payments')->update(['module_id' => 21]);
        DB::table('module_levels')->where('value', 'suscription_app_plans')->update(['module_id' => 21]);
        DB::table('module_levels')->where('value', 'pos_garage')->update(['module_id' => 1]);
        DB::table('module_levels')->where('value', 'dispatches')->update(['module_id' => 51]);
        DB::table('module_levels')->where('value', 'dispatch_carrier')->update(['module_id' => 51]);
        DB::table('module_levels')->where('value', 'dispatchers')->update(['module_id' => 51]);
        DB::table('module_levels')->where('value', 'drivers')->update(['module_id' => 51]);
        DB::table('module_levels')->where('value', 'transports')->update(['module_id' => 51]);

        DB::table('module_levels')->whereIn('id', [
            48,
        ])->delete();
        DB::table('modules')->whereIn('value', [
            'pos',
            'generate_link_app',
        ])->delete(); 

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        DB::table('module_levels')->insert([
            ['id'=>'48','value' => 'advanced_dispatches', 'description' => 'Guías de remisión', 'module_id' => 3],
        ]);

        DB::table('module_levels')->where('value', 'new_document')->update(['module_id' => 1]);
        DB::table('module_levels')->where('value', 'list_document')->update(['module_id' => 1]);
        DB::table('module_levels')->where('value', 'document_not_sent')->update(['module_id' => 1]);
        DB::table('module_levels')->where('value', 'document_contingengy')->update(['module_id' => 1]);
        DB::table('module_levels')->where('value', 'catalogs')->update(['module_id' => 1]);
        DB::table('module_levels')->where('value', 'summary_voided')->update(['module_id' => 1]);
        DB::table('module_levels')->where('value', 'quotations')->update(['module_id' => 1]);
        DB::table('module_levels')->where('value', 'sale_notes')->update(['module_id' => 1]);
        DB::table('module_levels')->where('value', 'incentives')->update(['module_id' => 1]);
        DB::table('module_levels')->where('value', 'sale-opportunity')->update(['module_id' => 1]);
        DB::table('module_levels')->where('value', 'contracts')->update(['module_id' => 1]);
        DB::table('module_levels')->where('value', 'order-note')->update(['module_id' => 1]);
        DB::table('module_levels')->where('value', 'technical-service')->update(['module_id' => 1]);
        DB::table('module_levels')->where('value', 'regularize_shipping')->update(['module_id' => 1]);
        DB::table('module_levels')->where('value', 'pos')->update(['module_id' => 1]);
        DB::table('module_levels')->where('value', 'cash')->update(['module_id' => 12]);
        DB::table('module_levels')->where('value', 'ecommerce')->update(['module_id' => 10]);
        DB::table('module_levels')->where('value', 'ecommerce_orders')->update(['module_id' => 10]);
        DB::table('module_levels')->where('value', 'ecommerce_items')->update(['module_id' => 10]);
        DB::table('module_levels')->where('value', 'ecommerce_tags')->update(['module_id' => 10]);
        DB::table('module_levels')->where('value', 'ecommerce_promotions')->update(['module_id' => 10]);
        DB::table('module_levels')->where('value', 'ecommerce_settings')->update(['module_id' => 10]);
        DB::table('module_levels')->where('value', 'items')->update(['module_id' => 17]);
        DB::table('module_levels')->where('value', 'items_packs')->update(['module_id' => 17]);
        DB::table('module_levels')->where('value', 'items_services')->update(['module_id' => 17]);
        DB::table('module_levels')->where('value', 'items_categories')->update(['module_id' => 17]);
        DB::table('module_levels')->where('value', 'items_brands')->update(['module_id' => 17]);
        DB::table('module_levels')->where('value', 'items_lots')->update(['module_id' => 17]);
        DB::table('module_levels')->where('value', 'clients')->update(['module_id' => 18]);
        DB::table('module_levels')->where('value', 'clients_types')->update(['module_id' => 18]);
        DB::table('module_levels')->where('value', 'purchases_create')->update(['module_id' => 2]);
        DB::table('module_levels')->where('value', 'purchases_list')->update(['module_id' => 2]);
        DB::table('module_levels')->where('value', 'purchases_orders')->update(['module_id' => 2]);
        DB::table('module_levels')->where('value', 'purchases_expenses')->update(['module_id' => 2]);
        DB::table('module_levels')->where('value', 'purchases_suppliers')->update(['module_id' => 2]);
        DB::table('module_levels')->where('value', 'purchases_quotations')->update(['module_id' => 2]);
        DB::table('module_levels')->where('value', 'purchases_fixed_assets_items')->update(['module_id' => 2]);
        DB::table('module_levels')->where('value', 'purchases_fixed_assets_purchases')->update(['module_id' => 2]);
        DB::table('module_levels')->where('value', 'inventory')->update(['module_id' => 8]);
        DB::table('module_levels')->where('value', 'inventory_transfers')->update(['module_id' => 8]);
        DB::table('module_levels')->where('value', 'inventory_devolutions')->update(['module_id' => 8]);
        DB::table('module_levels')->where('value', 'inventory_report_kardex')->update(['module_id' => 8]);
        DB::table('module_levels')->where('value', 'inventory_report')->update(['module_id' => 8]);
        DB::table('module_levels')->where('value', 'inventory_report_valued_kardex')->update(['module_id' => 8]);
        DB::table('module_levels')->where('value', 'users')->update(['module_id' => 14]);
        DB::table('module_levels')->where('value', 'users_establishments')->update(['module_id' => 14]);
        DB::table('module_levels')->where('value', 'advanced_retentions')->update(['module_id' => 3]);
        DB::table('module_levels')->where('value', 'advanced_perceptions')->update(['module_id' => 3]);
        DB::table('module_levels')->where('value', 'advanced_order_forms')->update(['module_id' => 3]);
        DB::table('module_levels')->where('value', 'account_report')->update(['module_id' => 9]);
        DB::table('module_levels')->where('value', 'account_formats')->update(['module_id' => 9]);
        DB::table('module_levels')->where('value', 'account_summary')->update(['module_id' => 9]);
        DB::table('module_levels')->where('value', 'finances_movements')->update(['module_id' => 12]);
        DB::table('module_levels')->where('value', 'finances_incomes')->update(['module_id' => 12]);
        DB::table('module_levels')->where('value', 'finances_unpaid')->update(['module_id' => 12]);
        DB::table('module_levels')->where('value', 'finances_to_pay')->update(['module_id' => 12]);
        DB::table('module_levels')->where('value', 'finances_payments')->update(['module_id' => 12]);
        DB::table('module_levels')->where('value', 'finances_balance')->update(['module_id' => 12]);
        DB::table('module_levels')->where('value', 'finances_payment_method_types')->update(['module_id' => 12]);
        DB::table('module_levels')->where('value', 'account_users_settings')->update(['module_id' => 11]);
        DB::table('module_levels')->where('value', 'account_users_list')->update(['module_id' => 11]);
        DB::table('module_levels')->where('value', 'hotels_reception')->update(['module_id' => 15]);
        DB::table('module_levels')->where('value', 'hotels_rates')->update(['module_id' => 15]);
        DB::table('module_levels')->where('value', 'hotels_floors')->update(['module_id' => 15]);
        DB::table('module_levels')->where('value', 'hotels_cats')->update(['module_id' => 15]);
        DB::table('module_levels')->where('value', 'hotels_rooms')->update(['module_id' => 15]);
        DB::table('module_levels')->where('value', 'documentary_offices')->update(['module_id' => 16]);
        DB::table('module_levels')->where('value', 'documentary_process')->update(['module_id' => 16]);
        DB::table('module_levels')->where('value', 'documentary_documents')->update(['module_id' => 16]);
        DB::table('module_levels')->where('value', 'documentary_actions')->update(['module_id' => 16]);
        DB::table('module_levels')->where('value', 'documentary_files')->update(['module_id' => 16]);
        DB::table('module_levels')->where('value', 'digemid')->update(['module_id' => 19]);
        DB::table('module_levels')->where('value', 'documentary_requirements')->update(['module_id' => 16]);
        DB::table('module_levels')->where('value', 'inventory_item_extra_data')->update(['module_id' => 8]);
        DB::table('module_levels')->where('value', 'configuration_company')->update(['module_id' => 5]);
        DB::table('module_levels')->where('value', 'configuration_advance')->update(['module_id' => 5]);
        DB::table('module_levels')->where('value', 'configuration_visual')->update(['module_id' => 5]);
        DB::table('module_levels')->where('value', 'advanced_purchase_settlements')->update(['module_id' => 3]);
        DB::table('module_levels')->where('value', 'suscription_app_client')->update(['module_id' => 21]);
        DB::table('module_levels')->where('value', 'suscription_app_service')->update(['module_id' => 21]);
        DB::table('module_levels')->where('value', 'suscription_app_payments')->update(['module_id' => 21]);
        DB::table('module_levels')->where('value', 'suscription_app_plans')->update(['module_id' => 21]);
        DB::table('module_levels')->where('value', 'pos_garage')->update(['module_id' => 1]);

        DB::table('module_levels')->whereIn('id', [90, 91, 92, 93, 94])->delete();
        DB::table('modules')->whereIn('value', [
            'preventa',
            'guia',
            'comprobante',
        ])->delete();
    }

}
