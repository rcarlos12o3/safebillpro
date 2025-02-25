<?php

namespace Modules\Hotel\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Hotel\Models\HotelFloor;
use Modules\Hotel\Http\Requests\HotelFloorRequest;
use App\Models\Tenant\Establishment;

class HotelFloorController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * @return Response
	 */
	public function index()
	{
		$user = auth()->user();

		$query = HotelFloor::with('establishment')->orderBy('id', 'DESC');

		if (request()->ajax()) {

			if (request('establishment_id') && $user->type === 'admin') {
				$query->where('establishment_id', request('establishment_id'));
			}

			if ($user->type != 'admin') {
				$query->where('establishment_id', $user->establishment_id);
			}

			$floors = $query->paginate(25);

			$floors->getCollection()->transform(function ($floor) {
				return [
					'id' => $floor->id,
					'description' => $floor->description,
					'active' => $floor->active,
					'establishment_id' => $floor->establishment_id,
					'establishment_name' => $floor->establishment->description ?? '',
				];
			});

			return response()->json([
				'success' => true,
				'floors' => $floors,
			], 200);
		}

        $query->where('establishment_id', $user->establishment_id);

		$floors = $query->paginate(25);
		
		$floors->getCollection()->transform(function ($floor) {
			return [
				'id' => $floor->id,
				'description' => $floor->description,
				'active' => $floor->active,
				'establishment_id' => $floor->establishment_id,
				'establishment_name' => $floor->establishment->description ?? '',
			];
		});

		$establishments = Establishment::select('id','description')->get();
		$userType = auth()->user()->type;
		$establishmentId = auth()->user()->establishment_id;

		return view('hotel::floors.index', compact('floors','establishments','userType','establishmentId'));
	}

	/**
	 * Store a newly created resource in storage.
	 * @param Request $request
	 * @return Response
	 */
	public function store(HotelFloorRequest $request)
	{
		$floor = HotelFloor::create($request->validated());

		return response()->json([
			'success' => true,
			'data'    => $floor
		], 200);
	}

	/**
	 * Update the specified resource in storage.
	 * @param Request $request
	 * @param int $id
	 * @return Response
	 */
	public function update(HotelFloorRequest $request, $id)
	{
		$floor = HotelFloor::findOrFail($id);
		$floor->fill($request->only('description', 'active','establishment_id'));
		$floor->save();

		return response()->json([
			'success' => true,
			'data'    => $floor
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
			HotelFloor::where('id', $id)
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
