<?php

namespace Modules\Hotel\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Hotel\Http\Requests\HotelAddRateToRoomRequest;
use Modules\Hotel\Models\HotelRoom;
use Modules\Hotel\Models\HotelFloor;
use Modules\Hotel\Models\HotelCategory;
use Modules\Hotel\Http\Requests\HotelRoomRequest;
use Modules\Hotel\Http\Requests\HotelFloorRequest;
use Modules\Hotel\Models\HotelRate;
use Modules\Hotel\Models\HotelRoomRate;
use App\Models\Tenant\Establishment;

class HotelRoomController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * @return Response
	 */
	public function index()
	{
		$user = auth()->user();

		$query = HotelRoom::with('establishment','category', 'floor')->orderBy('id', 'DESC');

		if (request()->ajax()) {

			if (request('establishment_id') && $user->type === 'admin') {
				$query->where('establishment_id', request('establishment_id'));
			}

			if ($user->type != 'admin') {
				$query->where('establishment_id', $user->establishment_id);
			}

			if (request('hotel_floor_id')) {
				$query->where('hotel_floor_id', request('hotel_floor_id'));
			}
			if (request('hotel_category_id')) {
				$query->where('hotel_category_id', request('hotel_category_id'));
			}
			if (request('status')) {
				$query->where('status', request('status'));
			}
			if (request('name')) {
				$query->where('name', 'like', '%' . request('name') . '%');
			}

			$rooms = $query->paginate(25);
			return response()->json([
				'success' => true,
				'rooms' => $rooms
			], 200);
		}

		$query->where('establishment_id', $user->establishment_id);

		$rooms = $query->paginate(25);

		$establishments = Establishment::select('id','description')->get();
		$userType = auth()->user()->type;
		$establishmentId = auth()->user()->establishment_id;

		$categories = HotelCategory::where('active', true)
			->where('establishment_id', $user->establishment_id)
			->orderBy('description')
			->get();

		$floors = HotelFloor::where('active', true)
			->where('establishment_id', $user->establishment_id)
			->orderBy('description')
			->get();

		$roomStatus = HotelRoom::$status;



		return view('hotel::rooms.index', compact('rooms', 'floors', 'categories', 'roomStatus','establishments','userType','establishmentId'));

	}

	/**
	 * Store a newly created resource in storage.
	 * @param Request $request
	 * @return Response
	 */
	public function store(HotelRoomRequest $request)
	{
		$room = HotelRoom::create($request->validated());
		$room->status = 'DISPONIBLE';
		$room->save();

		return response()->json([
			'success' => true,
			'data'    => $room
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
		$room = HotelRoom::findOrFail($id);
		$room = $room->fill($request->only('description', 'active', 'name', 'hotel_category_id', 'hotel_floor_id','establishment_id'));
		$room->save();

		return response()->json([
			'success' => true,
			'data'    => $room
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
			HotelRoom::where('id', $id)
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

	public function changeRoomStatus($roomId)
	{
		HotelRoom::where('id', $roomId)
			->update([
				'status' => request('status')
			]);

		return response()->json([
			'success' => true,
			'message' => 'La habitación cambió su estado a DISPONIBLE',
		], 200);
	}

	public function tables($id)
	{
		$user = auth()->user();

		$categories = $this->getTablesQuery(HotelCategory::class, $id, $user);
		$floors = $this->getTablesQuery(HotelFloor::class, $id, $user);
		$rates = $this->getTablesQuery(HotelRate::class, $id, $user);

		return response()->json([
			'success'    => true,
			'rates'      => $rates,
			'floors'     => $floors,
			'categories' => $categories
		], 200);
	}

	private function getTablesQuery($model, $id, $user)
	{
		return $model::where('active', true)
			->when($id > 0 || $user->type !== 'admin', function ($query) use ($id, $user) {
				$query->where('establishment_id', $id > 0 ? $id : $user->establishment_id);
			})
			->orderBy('description')
			->get();
	}

	public function myRates($roomId)
	{
		$myRates = HotelRoomRate::with('rate')
			->where('hotel_room_id', $roomId)
			->get();

		return response()->json([
			'success'      => true,
			'room_rates'   => $myRates,
		], 200);
	}

	public function addRateToRoom(HotelAddRateToRoomRequest $request, $roomId)
	{
		$roomRate = HotelRoomRate::create($request->only('hotel_room_id', 'hotel_rate_id', 'price'));
		$roomRate->load('rate');

		return response()->json([
			'success'     => true,
			'room_rate'   => $roomRate,
		], 200);
	}

	public function deleteRoomRate($roomId, $roomRateId)
	{
		HotelRoomRate::where('hotel_room_id', $roomId)
			->where('id', $roomRateId)
			->delete();

		return response()->json([
			'success'     => true,
			'message'     => 'Información actualizada',
		], 200);
	}
}
