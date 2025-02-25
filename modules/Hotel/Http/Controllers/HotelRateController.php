<?php

namespace Modules\Hotel\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Hotel\Models\HotelRate;
use Modules\Hotel\Http\Requests\HotelRateRequest;
use App\Models\Tenant\Establishment;

class HotelRateController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * @return Response
	 */
	public function index()
	{

		$user = auth()->user();

		$query = HotelRate::with('establishment')->orderBy('id', 'DESC');

		if (request()->ajax()) {
			if (request('establishment_id') && $user->type === 'admin') {
				$query->where('establishment_id', request('establishment_id'));
			}

			if ($user->type != 'admin') {
				$query->where('establishment_id', $user->establishment_id);
			}

			$rates = $query->paginate(25);

			$rates->getCollection()->transform(function ($rate) {
				return [
					'id' => $rate->id,
					'description' => $rate->description,
					'active' => $rate->active,
					'establishment_id' => $rate->establishment_id,
					'establishment_name' => $rate->establishment->description ?? '',
				];
			});

			return response()->json([
				'success' => true,
				'rates' => $rates,
			], 200);
		}

        $query->where('establishment_id', $user->establishment_id);

		$rates = $query->paginate(25);
		
		$rates->getCollection()->transform(function ($rate) {
			return [
				'id' => $rate->id,
				'description' => $rate->description,
				'active' => $rate->active,
				'establishment_id' => $rate->establishment_id,
				'establishment_name' => $rate->establishment->description ?? '',
			];
		});

		$establishments = Establishment::select('id','description')->get();
		$userType = auth()->user()->type;
		$establishmentId = auth()->user()->establishment_id;

		return view('hotel::rates.index', compact('rates','establishments','userType','establishmentId'));
	}

	/**
	 * Store a newly created resource in storage.
	 * @param Request $request
	 * @return Response
	 */
	public function store(HotelRateRequest $request)
	{
		$rate = HotelRate::create($request->validated());

		return response()->json([
			'success' => true,
			'data'    => $rate
		], 200);
	}

	/**
	 * Update the specified resource in storage.
	 * @param Request $request
	 * @param int $id
	 * @return Response
	 */
	public function update(HotelRateRequest $request, $id)
	{
		$rate = HotelRate::findOrFail($id);
		$rate->fill($request->only('description', 'active','establishment_id'));
		$rate->save();

		return response()->json([
			'success' => true,
			'data'    => $rate
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
			HotelRate::where('id', $id)
				->delete();

			return response()->json([
				'success' => true,
				'message' => 'Información actualizada'
			], 200);
		} catch (\Throwable $th) {
			return response()->json([
				'success'    => false,
				'message'    => 'Ocurrió un error al procesar su petición. Detalles: ' . $th->getMessage()
			], 500);
		}
	}
}
