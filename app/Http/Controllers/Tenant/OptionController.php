<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Document;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\Quotation;
use App\Models\Tenant\Kardex;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\Retention;
use App\Models\Tenant\Perception;
use App\Models\Tenant\Summary;
use App\Models\Tenant\Voided;
use Illuminate\Http\Request;
use App\Models\Tenant\Configuration;
use Modules\Expense\Models\Expense;
use Modules\Purchase\Models\PurchaseOrder;
use Modules\Finance\Models\GlobalPayment;
use Modules\Finance\Models\Income;
use Modules\Purchase\Models\PurchaseQuotation;
use Modules\Order\Models\OrderNote;
use Modules\Order\Models\OrderForm;
use Modules\Inventory\Models\{
    ItemWarehouse,
    InventoryKardex
};
use Modules\Sale\Models\SaleOpportunity;
use Modules\Sale\Models\Contract;
use Modules\Purchase\Models\FixedAssetPurchase;
use App\Models\Tenant\{
    CashDocumentCredit,
    CashDocument,
    ItemMovement,
    Inventory,
    Item
};
use Modules\Payment\Models\PaymentLink;
use Modules\MercadoPago\Models\Transaction;
use Modules\Pos\Models\Tip;
use Modules\Production\Models\{
    Production,
    Mill,
    Packaging,
};
use Modules\Purchase\Models\WeightedAverageCost;
use Illuminate\Support\Facades\DB;


class OptionController extends Controller
{

    protected $delete_quantity;

    public function create()
    {
        return view('tenant.options.form');
    }

    public function deleteDocuments(Request $request)
    {

        $this->delete_quantity = 0;

        Summary::where('soap_type_id', '01')->delete();
        Voided::where('soap_type_id', '01')->delete();

        //Purchase
        $this->deleteInventoryKardex(Purchase::class);

        Purchase::where('soap_type_id', '01')->delete();

        PurchaseOrder::where('soap_type_id', '01')->delete();
        PurchaseQuotation::where('soap_type_id', '01')->delete();

        $quantity = Document::where('soap_type_id', '01')->count();

        //Document
        $this->deleteInventoryKardex(Document::class);

        Document::where('soap_type_id', '01')
        ->whereIn('document_type_id', ['07', '08'])->delete();

        $this->deleteRecordsCash(Document::class);
        // Document::where('soap_type_id', '01')->delete();

        $this->update_quantity_documents($quantity);

        Retention::where('soap_type_id', '01')->delete();
        Perception::where('soap_type_id', '01')->delete();

        //SaleNote
        $sale_notes = SaleNote::where('soap_type_id', '01')->get();
        // SaleNote::where('soap_type_id', '01')->delete();

        $this->deleteRecordsCash(SaleNote::class);

        $this->deleteInventoryKardex(SaleNote::class, $sale_notes);


        Contract::where('soap_type_id', '01')->delete();
        // Quotation::where('soap_type_id', '01')->delete();
        $this->deleteQuotation();

        SaleOpportunity::where('soap_type_id', '01')->delete();

        Expense::where('soap_type_id', '01')->delete();
        OrderNote::where('soap_type_id', '01')->delete();
        OrderForm::where('soap_type_id', '01')->delete();

        GlobalPayment::where('soap_type_id', '01')->delete();
        Tip::where('soap_type_id', '01')->delete();

        Income::where('soap_type_id', '01')->delete();

        FixedAssetPurchase::where('soap_type_id', '01')->delete();

        $this->updateStockAfterDelete();

        $this->deletePaymentLink();

        // produccion

        Production::where('soap_type_id', '01')->delete();
        Packaging::where('soap_type_id', '01')->delete();
        $this->deleteMill();

        return [
            'success' => true,
            'message' => 'Documentos de prueba eliminados',
            'delete_quantity' => $this->delete_quantity,
        ];
    }


    /**
     *
     * Eliminar links de pago y transacciones asociadas en demo
     *
     * @return void
     */
    private function deletePaymentLink()
    {
        $transactions = Transaction::where('soap_type_id', '01')->get();

        foreach ($transactions as $transaction)
        {
            $transaction->transaction_queries()->delete();
            $transaction->delete();
        }

        PaymentLink::where('soap_type_id', '01')->delete();
    }


    /**
     *
     * Eliminar registros de ingresos de insumos
     *
     * @return void
     */
    private function deleteMill()
    {
        $mills = Mill::where('soap_type_id', '01')->get();

        foreach ($mills as $mill)
        {
            $mill->relation_mill_items()->delete();
            $mill->delete();
        }

    }

    /**
     *
     * Eliminar registros relacionados en caja y cotizaciones
     *
     * @return void
     */
    private function deleteQuotation()
    {
        $records_id = Quotation::where('soap_type_id', '01')->whereFilterWithOutRelations()->select('id')->get()->pluck('id')->toArray();
        // dd($records_id);
        CashDocument::whereIn('quotation_id', $records_id)->delete();
        Quotation::where('soap_type_id', '01')->delete();
    }


    /**
     *
     * Eliminar registros relacionados en caja - notas de venta/cpe
     *
     * @return void
     */
    private function deleteRecordsCash($model)
    {
        $records_id = $model::where('soap_type_id', '01')->whereFilterWithOutRelations()->select('id')->get()->pluck('id')->toArray();

        $column = ($model === Document::class) ? 'document_id' : 'sale_note_id';

        CashDocumentCredit::whereIn($column, $records_id)->delete();

        $model::where('soap_type_id', '01')->delete();
    }


    private function deleteInventoryKardex($model, $records = null){

        if(!$records){
            $records = $model::where('soap_type_id', '01')->get();
        }

        $this->delete_quantity += $records->count();

        foreach ($records as $record) {

            $record->inventory_kardex()->delete();

        }
    }

    private function updateStockAfterDelete(){

        // if($this->delete_quantity > 0){

        //     ItemWarehouse::latest()->update([
        //         'stock' => 0
        //     ]);

        // }

    }

    private function update_quantity_documents($quantity)
    {
        $configuration = Configuration::first();
        $configuration->quantity_documents -= $quantity;
        $configuration->save();
    }

    public function deleteItems(Request $request)
    {   
        $id_items_movement = ItemMovement::distinct()->pluck('item_id');
        $id_items_inventory = Inventory::distinct()->where('description','<>','Stock inicial')->pluck('item_id');
        $ids_item_merge = $id_items_movement->merge($id_items_inventory)->unique();
        $ids_item_merge_array = $ids_item_merge->toArray();
        $deletedItem = 0;
    
        try{
            DB::transaction(function () use ($ids_item_merge_array,&$deletedItem) {
                $ids_item_delete = Item::whereNotIn('id', $ids_item_merge_array)->pluck('id');
                $ids_item_delete_array = $ids_item_delete->toArray();
                
                InventoryKardex::whereIn('item_id', $ids_item_delete_array)->delete();
                Kardex::whereIn('item_id', $ids_item_delete_array)->delete();
                WeightedAverageCost::whereIn('item_id',$ids_item_delete_array)->delete();
                Inventory::whereIn('item_id',$ids_item_delete_array)->delete();
                
                $deletedItem = Item::whereIn('id', $ids_item_delete_array)->delete();
            });

            return [
                'success' => true,
                'message' => ($deletedItem==1)?$deletedItem.' Producto eliminado':$deletedItem.' Productos eliminados',
                'delete_quantity' => $deletedItem,
            ];

        }catch(\Exception $ex){
            return [
                'success' => false,
                'message' => 'Inconvenientes al eliminar',
                'delete_quantity' => $deletedItem,
            ];
        }
        
    }

}