<?php

namespace Modules\Purchase\Helpers;

use Modules\Inventory\Models\ItemWarehouse;
use Modules\Purchase\Models\WeightedAverageCost;
use App\Models\Tenant\{
    PurchaseItem,
    Item
};


class WeightedAverageCostHelper
{

    /**
     *
     * @param  int $item_id
     * @return int
     */
    public function rowsQuantityByItem($item_id)
    {
        return WeightedAverageCost::where('item_id', $item_id)->count();
    }


    /**
     *
     * @param  int $item_id
     * @return float
     */
    public function sumStockItemWarehouse($item_id)
    {
        return ItemWarehouse::where('item_id', $item_id)->sum('stock');
    }


    /**
     *
     * Obtener solo el ultimo costo ponderado
     *
     * @return float
     */
    public static function onlyWeightedAverageCost($item_id)
    {
        $row_weighted_cost = WeightedAverageCost::findLastWeightedAverageCost($item_id, true);

        return number_format(optional($row_weighted_cost)->weighted_cost, 2, '.', '');
    }


    /**
     *
     * @param  int $item_id
     * @return WeightedAverageCost
     */
    public function getLastWeightedAverageCost($item_id)
    {
        return WeightedAverageCost::findLastWeightedAverageCost($item_id, false);
    }


    /**
     *
     * @param  float $last_stock
     * @param  float $last_weighted_cost
     * @param  float $total
     * @param  float $stock
     * @return float
     */
    public function calculateWeightedCost($last_stock, $last_weighted_cost, $total, $stock)
    {
        return round(($last_stock * $last_weighted_cost + $total) / $stock, 2);
    }


    /**
     * createWeightedAverageCost
     *
     * @param  PurchaseItem|Item $model
     * @param  array $data
     * @return WeightedAverageCost
     */
    public function createWeightedAverageCost($model, $data)
    {
        return $model->weighted_average_cost()->create($data);
    }


    /**
     *
     * Filtrar productos sin registro relacionado en costos ponderados
     *
     * @return array
     */
    public function getItemsWithoutWeightedCost()
    {
        return Item::whereFilterWithOutRelations()
                    ->whereIsNotService()
                    ->whereNotIsSet()
                    ->whereDoesntHave('weighted_average_costs')
                    ->get();
    }


    /**
     *
     * @param  PurchaseItem $purchase_item
     * @param  float $last_stock
     * @param  int $factor
     * @return float
     */
    public function calculateStock($purchase_item, $last_stock, $factor = 1)
    {
        // $presentation_quantity = $this->getPresentationQuantity($purchase_item);
        $presentation_quantity = 1;

        return $last_stock + (($purchase_item->quantity * $factor) * $presentation_quantity);
    }


    /**
     *
     * @param  PurchaseItem $purchase_item
     * @return float
     */
    private function getPresentationQuantity($purchase_item)
    {
        return (!empty($purchase_item->item->presentation)) ? $purchase_item->item->presentation->quantity_unit : 1;
    }


    /**
     *
     * @param  Item $item
     * @return WeightedAverageCost
     */
    public function saveWeightedCostFromItem(Item $item, $is_created = true)
    {
        $cost = $item->purchase_unit_price ? $item->purchase_unit_price : 0;
        $stock = $is_created ? $item->stock : $this->sumStockItemWarehouse($item->id);

        return $this->createWeightedAverageCost($item, [
            'item_id' => $item->id,
            'date_of_issue' => date('Y-m-d'),
            'quantity' => $stock,
            'cost' => $cost,
            'total' => $stock * $cost,
            'weighted_cost' => $cost,
            'stock' => $stock,
        ]);
    }


    /**
     *
     * @param  PurchaseItem $purchase_item
     * @return void
     */
    // public function voidedWeightedAverageCost(PurchaseItem $purchase_item)
    // {
    //     if($purchase_item->item->unit_type_id == Item::SERVICE_UNIT_TYPE || $purchase_item->item->is_set) return;

    //     $this->processWeightedAverageCost($purchase_item, -1);
    // }


    // /**
    //  *
    //  * @param  PurchaseItem $purchase_item
    //  * @return void
    //  */
    // public function newWeightedAverageCost(PurchaseItem $purchase_item)
    // {
    //     if($purchase_item->item->unit_type_id == Item::SERVICE_UNIT_TYPE || $purchase_item->item->is_set) return;

    //     if($this->rowsQuantityByItem($purchase_item->item_id) == 0) return;

    //     $this->processWeightedAverageCost($purchase_item, 1);
    // }


    /**
     *
     * @param  PurchaseItem $purchase_item
     * @param  int $factor
     * @return void
     */
    public function processWeightedAverageCost(PurchaseItem $purchase_item, $factor)
    {

        if($purchase_item->item->unit_type_id == Item::SERVICE_UNIT_TYPE || $purchase_item->item->is_set) return;

        if($this->rowsQuantityByItem($purchase_item->item_id) == 0) return;

        $item_id = $purchase_item->item_id;

        $last_weighted_average_cost = $this->getLastWeightedAverageCost($item_id);

        $last_stock = $last_weighted_average_cost->stock;

        $stock = $this->calculateStock($purchase_item, $last_stock, $factor);

        if($stock == 0 && $factor === -1)
        {
            $weighted_cost = $purchase_item->unit_price;
        }
        else
        {
            $weighted_cost = $this->calculateWeightedCost($last_stock, $last_weighted_average_cost->weighted_cost, ($purchase_item->total * $factor), $stock);
        }

        $this->createWeightedAverageCost($purchase_item, [
            'item_id' => $item_id,
            'date_of_issue' => date('Y-m-d'),
            'quantity' => $purchase_item->quantity * $factor,
            'cost' => $purchase_item->unit_price,
            'total' => $purchase_item->total * $factor,
            'weighted_cost' => $weighted_cost,
            'stock' => $stock,
        ]);
    }


}
