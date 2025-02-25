<?php

namespace Modules\Inventory\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Inventory\Models\ItemWarehouse;
use Carbon\Carbon;


class ReportKardexLotsGroupCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        return $this->collection->transform(function($row, $key) use ($request) {

            $quantity = $row->quantity;
            if($request->warehouse_id && $request->warehouse_id != 'all') {
                $transfers = ItemWarehouse::where('item_id', $row->item_id)
                    ->where('warehouse_id', $request->warehouse_id)
                    ->first();
                $quantity = $transfers->stock;
            }

            if($request->warehouse_id && $request->warehouse_id == 'all') {
                $transfers = ItemWarehouse::where('item_id', $row->item_id)
                    ->sum('stock');
                $quantity = $transfers;
            }

            $diff = '';

            if($row->date_of_due)
            {
                $now = Carbon::now();
                $due =   Carbon::parse($row->date_of_due);
                $diff = $now->diffInDays($due);
            }

            return [
                'id' => $row->id,
                'code' => $row->code,
                'quantity' => $quantity,
                'date_of_due' => $row->date_of_due,
                'name_item' => $row->item->description,
                'und_item' => $row->item->unit_type_id,
                'code_item' => $row->item->internal_id,
                'diff_days' => $diff,
            ];
        });
    }




}
