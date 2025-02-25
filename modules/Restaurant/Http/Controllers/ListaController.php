<?php

namespace Modules\Restaurant\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Models\Tenant\Item;
use Modules\Item\Models\Category;
use Modules\Inventory\Models\InventoryConfiguration;
use Modules\Restaurant\Http\Resources\ItemCollection;
use App\Models\Tenant\Promotion;
use App\Http\Controllers\Tenant\Api\ServiceController;
use App\Models\Tenant\ConfigurationEcommerce;
use App\Models\Tenant\Order;
use Exception;
use Illuminate\Support\Facades\Validator;
use stdClass;
use Illuminate\Support\Str;
use App\Http\Controllers\Tenant\EmailController;
use App\Mail\Tenant\CulqiEmail;
use Modules\Restaurant\Models\RestaurantConfiguration;
use Modules\Restaurant\Models\RestaurantNote;


class ListaController extends Controller
{

    public function index()
{
    $categories = Category::all();

    $categoriesWithProducts = $categories->map(function ($category) {
        $products = Item::where('apply_restaurant', 1)
            ->where('category_id', $category->id)
            ->get();

        return [
            'category' => $category,
            'products' => $products,
        ];
    });
    return view('restaurant::lista', compact('categoriesWithProducts'));
}


}
