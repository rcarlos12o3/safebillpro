<?php

namespace Modules\Inventory\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ReportTemporaryKardexCollection extends ResourceCollection
{

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        return $this->collection->transform(function($row, $key)  {

            return [
                'id' => $row->inventory_kardex_id,
                'date_time' => $row->date_time,
                'date_of_issue' => $row->date_of_issue,
                'number' => $row->number,
                'inventory_kardexable_type' => $row->inventory_kardexable_type,
                'item_name'=> $row->item_name,
                'item_warehouse_price' => $row->item_warehouse_price,
                'doc_asoc' => $row->doc_asoc,
                'order_note_asoc' => $row->order_note_asoc,
                'sale_note_asoc' => $row->sale_note_asoc,
                'input' => $row->input,
                'output' => $row->output,
                'balance' => $row->balance,
                'type_transaction' => $row->type_transaction,
                'warehouse' => $row->warehouse,
                'date_of_register' => $row->date_of_register,
                'guide_id' => $row->guide_id,
            ];

        });
    }




}
