<?php
namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant\Cash;
use App\Models\Tenant\CashDocument;
use App\Models\Tenant\Document;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\CashDocumentCredit;


class CashController extends Controller
{

    /**
     * web service para recibo de documentos junto con caja
     * apertura y cierre de caja
     * creacion de relaciones de documentos con caja (notas de venta y facturas/boletas)
     *
     * @param $request {}
     * example:
     * beginningBalance: number
     * dateOpening: "Y-m-d"
     * timeOpening: "H:m:s"
     * internalsId: [{external_id: String, type: String NOTA|BOLETA|'' }]
     */
    public function storeRestaurant(Request $request) {

        $cash = new Cash();
        $cash->user_id = auth()->user()->id;
        $cash->date_opening = $request->dateOpening;
        $cash->time_opening = $request->timeOpening;
        $cash->date_closed = date('Y-m-d');
        $cash->time_closed = date('H:i:s');
        $cash->beginning_balance = (float)$request->beginningBalance;
        $cash->final_balance = 0;
        $cash->income = 0;
        $cash->state = 0;
        $cash->reference_number = $request->referenceNumber;
        $cash->apply_restaurant = 1;

        $cash->save();

        $total_documents = 0;

        // se recorren todos los externals id de la caja anteriormente creada
        // para registrar la relación con ella y acumular el monto total
        foreach ($request->internalsId as $row) {
            if($row['type'] == 'NOTA'){
                $sale_note = SaleNote::where('external_id', $row['external_id'])->first();
                $total_documents += (float)$sale_note->total;

                CashDocument::create([
                    'cash_id' => $cash->id,
                    'sale_note_id' => $sale_note->id,
                ]);
            } else {
                $document = Document::where('external_id', $row['external_id'])->first();
                $total_documents += (float)$document->total;

                CashDocument::create([
                    'cash_id' => $cash->id,
                    'document_id' => $document->id,
                ]);
            }
        }

        // se toman los montos anteriores para cerrar la caja
        $cash->income = $total_documents;
        $cash->final_balance = $cash->beginning_balance + $cash->income;
        $cash->save();





        return [
            'success' => true,
            'message' => 'Caja creada con éxito'
        ];
    }

    public function cash_document(Request $request) {

        $cash = Cash::where([
                                ['id', $request->cash_id],
                                ['state', true],
                            ])->first();
        
        (int)$payment_credit = 0;
        

        if($request->document_id != null) {
            $document_id = $request->document_id;

            $document =  Document::find((int)$document_id);

                                            //credito
            if($document->payment_condition_id == '02')  {
                CashDocumentCredit::create([
                    'cash_id' => $cash->id,
                    'document_id' => $document_id
                ]);

                $payment_credit += 1;
            }
        }
        else if($request->sale_note_id != null) {

             $document_id = $request->sale_note_id;

             $document =  SaleNote::find((int)$document_id);

                                                //credito
             if($document->payment_method_type_id == '09')  {
                CashDocumentCredit::create([
                    'cash_id' => $cash->id,
                    'sale_note_id' => $document_id
                ]);

                $payment_credit += 1;
            }
        }

        if($payment_credit == 0) {

            $req = [
                'document_id' => $request->document_id,
                'sale_note_id' => $request->sale_note_id,
                'quotation_id' => $request->quotation_id,
            ];

            $cash->cash_documents()->updateOrCreate($req);
        }
        
        return [
            'success' => true,
            'message' => 'Venta con éxito',
        ];
    }

    public function opening_cash()
    {
        $cash = Cash::where([['user_id', auth()->user()->id],['state', true]])->first();

        return [
            'success' => ($cash)?true:false,
            'message' => 'Verificar si existe caja abierta',
            'data' => [
                'cash_id' => ($cash)?$cash->id:null,
                'description' => ($cash)?$cash->reference_number . " " . $cash->date_opening . " (" . $cash->user->name . ")":'',
            ]
        ];
    }

    public function opening_cash_check($cash_id)
    {
        $cash = Cash::where([['id', $cash_id],['state', true]])->first();

        return [
            'success' => ($cash)?true:false,
            'message' => 'Verificar si existe caja abierta',
            'data' => [
                'cash_id' => ($cash)?$cash->id:null,
                'description' => ($cash)?$cash->reference_number . " " . $cash->date_opening . " (" . $cash->user->name . ")":'',
            ]
        ];
    }

    public function cash_available()
    {
        $cash = Cash::where('state', true)
            ->with('user')
            ->get()
            ->map(function($cash) { 
                return [
                    'id' => $cash->id,
                    'description' => $cash->reference_number . " " . $cash->date_opening . " (" . $cash->user->name . ")",
                ];
            });
        
        return [
            'success' => true,
            'message' => 'Cajas disponibles',
            'data' => ($cash)?$cash:[]
        ];
    }

    public function close() {


        $cash = Cash::where('user_id',auth()->user()->id)->where('state',1)->first();

        if(!$cash){
            return [
                'success' => false,
                'message' => 'Caja no encontrada',
            ];
        }

        $cash->date_closed = date('Y-m-d');
        $cash->time_closed = date('H:i:s');

        $final_balance = 0;
        $income = 0;

        foreach ($cash->cash_documents as $cash_document) {


            if($cash_document->sale_note){

                if(in_array($cash_document->sale_note->state_type_id, ['01','03','05','07','13'])){
                    $final_balance += ($cash_document->sale_note->currency_type_id == 'PEN') ? $cash_document->sale_note->total : ($cash_document->sale_note->total * $cash_document->sale_note->exchange_rate_sale);
                }

                // $final_balance += $cash_document->sale_note->total;

            }
            else if($cash_document->document){

                if(in_array($cash_document->document->state_type_id, ['01','03','05','07','13'])){
                    $final_balance += ($cash_document->document->currency_type_id == 'PEN') ? $cash_document->document->total : ($cash_document->document->total * $cash_document->document->exchange_rate_sale);
                }

                // $final_balance += $cash_document->document->total;

            }
            else if($cash_document->expense_payment){

                if($cash_document->expense_payment->expense->state_type_id == '05'){
                    $final_balance -= ($cash_document->expense_payment->expense->currency_type_id == 'PEN') ? $cash_document->expense_payment->payment:($cash_document->expense_payment->payment  * $cash_document->expense_payment->expense->exchange_rate_sale);
                }

                // $final_balance -= $cash_document->expense_payment->payment;

            }
            else if($cash_document->purchase){
                if(in_array($cash_document->purchase->state_type_id, ['01','03','05','07','13'])){
                    if($cash_document->purchase->total_canceled == 1) {
                        $final_balance -= ($cash_document->purchase->currency_type_id == 'PEN') ? $cash_document->purchase->total : ($cash_document->purchase->total * $cash_document->purchase->exchange_rate_sale);
                    }
                }
            }
            // cotizacion
            else if($cash_document->quotation)
            {
                $final_balance += ($cash_document->quotation->applyQuotationToCash()) ? $cash_document->quotation->getTransformTotal() : 0;
            }

        }

        $cash->final_balance = round($final_balance + $cash->beginning_balance, 2);
        $cash->income = round($final_balance, 2);
        $cash->state = false;
        $cash->save();

        return [
            'success' => true,
            'message' => 'Caja cerrada con éxito',
        ];

    }

}
