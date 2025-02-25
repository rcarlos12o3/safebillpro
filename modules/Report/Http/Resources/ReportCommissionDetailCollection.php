<?php

namespace Modules\Report\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Report\Helpers\UserCommissionHelper;
use App\CoreFacturalo\Helpers\Template\ReportHelper;


class ReportCommissionDetailCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->transform(function($row, $key) {
            $type_document = '';
            $relation = $row->document_id ? $row->document : $row->sale_note;

            if($row->document_id) {
                $type_document =  $row->document->document_type_id == '01' ? 'FACTURA' : 'BOLETA';
                // $type_document =  $row->document->document_type_id == '03' ? 'FACTURA' : 'BOLETA';
            }
            else if ($row->sale_note_id) {
                $type_document = 'NOTA DE VENTA';
            }

            /*
            $purchase_unit_price = 0;
            if(isset($row->item->purchase_unit_price)){
                $purchase_unit_price = $row->item->purchase_unit_price;
            }
            */

            /* Cambios de Raul fixed | https://gitlab.com/facturaloperu/facturador/pro5/-/issues/1399 | calculo de utilidad detallada de productos vendidos por listado de presentacion
            pendiente de revisar y analizar

            $unit_gain = ((float)$row->unit_price - (float)$purchase_unit_price);
            $overall_profit = (((float)$row->unit_price * $row->quantity ) - ((float)$purchase_unit_price * $row->quantity));
            if(!empty($row->item->presentation)) {
                $presentation = $row->item->presentation;
                $unit_gain = ((float)$row->unit_price - (float)($purchase_unit_price * $presentation->quantity_unit));
                $overall_profit = (((float)$row->unit_price * $row->quantity ) - ((float)($purchase_unit_price * $presentation->quantity_unit) * $row->quantity));
            }
            */

            $commission_values = ReportHelper::getValuesReportCommissionDetail($row);
            
            return [
                'id' => $row->id,
                'date_of_issue' => $relation->date_of_issue->format('Y-m-d'),
                'type_document' => $type_document,
                'serie' => $relation->number_full,
                'customer_number' => $relation->customer->number,
                'customer_name' => $relation->customer->name,
                'name' => $row->relation_item->description,
                'quantity' => $commission_values->quantity,
                'purchase_unit_price' => $commission_values->purchase_unit_price,
                'unit_price' => $commission_values->sale_unit_price,
                'unit_gain' => $commission_values->unit_gain,
                'overall_profit' => $commission_values->overall_profit,
            ];
        });
    }

}
