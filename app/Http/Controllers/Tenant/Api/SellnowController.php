<?php

namespace App\Http\Controllers\Tenant\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Models\Tenant\Item;
use Modules\Item\Models\Category;
use Modules\Inventory\Models\InventoryConfiguration;
use Modules\Restaurant\Http\Resources\ItemCollection;
use App\Http\Controllers\Tenant\Api\ServiceController;
use App\Models\Tenant\Order;
use Exception;
use Illuminate\Support\Facades\Validator;
use stdClass;
use Illuminate\Support\Str;


class SellnowController extends Controller
{

    public function items(Request $request){

        $warehouse_id = auth()->user()->establishment->id;

        $items = Item::whereNotNull('internal_id')
            ->whereHas('warehouses', function ($query) use ($warehouse_id) {
                $query->where('warehouse_id', $warehouse_id);
            })
            ->orderBy('favorite','desc')
            ->get();

        $records = new ItemCollection($items);

        return [
            'success' => true,
            'data' => $records
        ];
    }

    public function categories(Request $request){
        $records = Category::all();
        return [
            'success' => true,
            'data' => $records
        ];
    }

    public function setFavoriteItem(Request $request) {
        $item = Item::findOrFail($request->id);

        $item->favorite = ($item->favorite == 1) ? 0 : 1;
        $item->save();

        return [
            'success' => true,
            'message' => ($item->favorite == 1)? "Producto agregado a favoritos": "Producto quitado de favoritos"
        ];
    }

}
