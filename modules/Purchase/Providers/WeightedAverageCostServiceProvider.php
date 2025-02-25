<?php

namespace Modules\Purchase\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Tenant\{
    PurchaseItem,
    Item
};
use Modules\Inventory\Models\ItemWarehouse;
use Modules\Purchase\Models\WeightedAverageCost;
use Modules\Purchase\Helpers\WeightedAverageCostHelper;


class WeightedAverageCostServiceProvider extends ServiceProvider
{

    public function register()
    {
    }


    public function boot()
    {
        $this->createWeightedAverageCost();
    }


    private function createWeightedAverageCost()
    {
        $this->observeCreatedPurchaseItem();
        $this->observeDeletedPurchaseItem();
        $this->observeCreatedItem();
    }


    /**
     * @return void
     */
    private function observeDeletedPurchaseItem()
    {
        PurchaseItem::deleted(function ($purchase_item) {
            (new WeightedAverageCostHelper)->processWeightedAverageCost($purchase_item, -1);
        });
    }


    /**
     * @return void
     */
    private function observeCreatedPurchaseItem()
    {
        PurchaseItem::created(function ($purchase_item) {
            (new WeightedAverageCostHelper)->processWeightedAverageCost($purchase_item, 1);
        });
    }


    /**
     * @return void
     */
    private function observeCreatedItem()
    {
        Item::created(function ($item) {

            if($item->hasServiceUnitType() || $item->checkIsSet()) return;

            (new WeightedAverageCostHelper)->saveWeightedCostFromItem($item);

        });
    }

}
