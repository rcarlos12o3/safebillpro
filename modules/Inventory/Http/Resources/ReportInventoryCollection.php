<?php

namespace Modules\Inventory\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\Tenant\ExchangeRate;

class ReportInventoryCollection extends ResourceCollection
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
            $item = $row->item;

            $sale_unit_price = $item->sale_unit_price;
            $purchase_unit_price = $item->purchase_unit_price;
            if($item->currency_type_id === 'USD') {
                $exchange = ExchangeRate::get()->last();
                $sale_unit_price = $sale_unit_price * $exchange->sale;
                $purchase_unit_price = $purchase_unit_price * $exchange->sale;
            }
            return [
                'barcode' => $item->barcode,
                'internal_id' => $item->internal_id,
                'name' => $item->description,
                'description' => $item->name,
                'item_category_name' => optional($item->category)->name,
                'stock_min' => $item->stock_min,
                'stock' => $row->stock,
                'sale_unit_price' => $sale_unit_price,
                'purchase_unit_price' => $purchase_unit_price,
                'profit'=>number_format($sale_unit_price-$purchase_unit_price,2,'.',''),
                'model' => $item->model,
                'brand_name' => $item->brand->name,
                'date_of_due' => optional($item->date_of_due)->format('d/m/Y'),
                'warehouse_name' => $row->warehouse->description,
                'currency_type_id' => $item->currency_type_id,
            ];

        });
    }




}
