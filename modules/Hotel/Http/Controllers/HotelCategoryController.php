<?php

namespace Modules\Hotel\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Hotel\Models\HotelCategory;
use Modules\Hotel\Http\Requests\HotelCategoryRequest;
use App\Models\Tenant\Establishment;

class HotelCategoryController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * @return Response
	 */
	public function index()
	{
		$user = auth()->user();

		$query = HotelCategory::with('establishment')->orderBy('id', 'DESC');

		if (request()->ajax()) {
			if (request('establishment_id') && $user->type === 'admin') {
				$query->where('establishment_id', request('establishment_id'));
			}

			if ($user->type != 'admin') {
				$query->where('establishment_id', $user->establishment_id);
			}

			$categories = $query->paginate(25);

			$categories->getCollection()->transform(function ($category) {
				return [
					'id' => $category->id,
					'description' => $category->description,
					'image' => $category->image,
					'active' => $category->active,
					'establishment_id' => $category->establishment_id,
					'establishment_name' => $category->establishment->description ?? '',
				];
			});

			return response()->json([
				'success' => true,
				'categories' => $categories,
			], 200);
		}

        $query->where('establishment_id', $user->establishment_id);

		$categories = $query->paginate(25);
		
		$categories->getCollection()->transform(function ($category) {
			return [
				'id' => $category->id,
				'description' => $category->description,
				'image' => $category->image,
				'active' => $category->active,
				'establishment_id' => $category->establishment_id,
				'establishment_name' => $category->establishment->description ?? '',
			];
		});

		$establishments = Establishment::select('id','description')->get();
		$userType = auth()->user()->type;
		$establishmentId = auth()->user()->establishment_id;

		return view('hotel::categories.index', compact('categories','establishments','userType','establishmentId'));

	}

	/**
	 * Store a newly created resource in storage.
	 * @param Request $request
	 * @return Response
	 */
	public function store(HotelCategoryRequest $request)
	{
		$category = HotelCategory::create($request->validated());

		return response()->json([
			'success' => true,
			'data'    => $category
		], 200);
	}

	/**
	 * Update the specified resource in storage.
	 * @param Request $request
	 * @param int $id
	 * @return Response
	 */
	public function update(HotelCategoryRequest $request, $id)
	{
		$category = HotelCategory::findOrFail($id);
		$category->fill($request->only('description', 'active','establishment_id'));
		$category->save();

		return response()->json([
			'success' => true,
			'data'    => $category
		], 200);
	}

	/**
	 * Remove the specified resource from storage.
	 * @param int $id
	 * @return Response
	 */
	public function destroy($id)
	{
		try {
			HotelCategory::where('id', $id)
				->delete();

			return response()->json([
				'success' => true,
				'message' => 'Información actualizada'
			], 200);
		} catch (\Throwable $th) {
			return response()->json([
				'success' => false,
				'data'    => 'Ocurrió un error al procesar su petición. Detalles: ' . $th->getMessage()
			], 500);
		}
	}
}
