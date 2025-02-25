<?php

namespace Modules\MobileApp\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Item\Models\Category;
use Modules\Item\Http\Resources\CategoryCollection;

class CategoryController extends Controller
{

    public function records()
    {
        return [
            'success' => true,
            'data' => new CategoryCollection(Category::get())
        ];
    }

    public function register(Request $request)
    {
        $name = $request->input('name');
        if ($name) {
            $present_category = Category::where('name', $request->input('name'))->first();

            if ($present_category) {
                return response([
                    'success' => false,
                    'message' => 'Esa categoría ya esta registrada'
                ], 400);
            }

            $present_category = new Category;
            $present_category->fill($request->all());
            $present_category->save();

            return [
                "success" => true,
                "category" => $present_category
            ];
        }

        return response([
            'success' => false,
            'message' => 'El nombre de Categoría debe ser obligatorio'
        ], 422);
    }
}
