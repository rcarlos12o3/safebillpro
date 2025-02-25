<?php

namespace Modules\Dispatch\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Person;
use Modules\ApiPeruDev\Data\ServiceData;
use Modules\Dispatch\Http\Requests\DispatchAddressRequest;
use Modules\Dispatch\Models\DispatchAddress;
use Illuminate\Http\Request;
use Modules\Dispatch\Http\Resources\{
    DispatchAddressCollection,
    DispatchAddressResource
};
use App\Models\Tenant\PersonAddress;

class DispatchAddressController extends Controller
{

    public function index()
    {
        return view('tenant.dispatches.dispatch-addresses.index');
    }


    public function columns()
    {
        return [
            'address' => 'Dirección',
        ];
    }


    /**
     *
     * @param  int $id
     * @return DispatchAddressResource
     */
    public function record($id)
    {
        return new DispatchAddressResource(DispatchAddress::findOrFail($id));
    }


    /**
     *
     * Listado
     *
     * @param  Request $request
     * @return DispatchAddressCollection
     */
    public function records(Request $request)
    {
        $records = DispatchAddress::whereFilterRecords($request);

        return new DispatchAddressCollection($records->paginate(config('tenant.items_per_page')));
    }


    public function tables()
    {
        $locations = func_get_locations();

        return [
            'locations' => $locations
        ];
    }

    public function store(DispatchAddressRequest $request)
    {
        $id = $request->input('id');
        $record = DispatchAddress::query()->firstOrNew(['id' => $id]);
        $record->fill($request->all());
        $record->save();

        return [
            'success' => true,
            'id' => $record->id
        ];
    }

    public function destroy($id)
    {
        DispatchAddress::query()
            ->find($id)
            ->update([
                'is_active' => false,
            ]);

        return [
            'success' => true,
            'message' => 'Dirección eliminada con éxito'
        ];
    }

    public function getOptions($person_id)
    {
        return DispatchAddress::query()
            ->where('person_id', $person_id)
            ->get()
            ->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'location_id' => $row->location_id,
                    'address' => $row->address,
                    'code' => $row->establishment_code
                ];
            });
    }

    public function searchAddress($person_id)
    {
        $person = Person::query()->find($person_id);
        if ($person->identity_document_type_id === '1') {
            $type = 'dni';
        } elseif ($person->identity_document_type_id === '6') {
            $type = 'ruc';
        } else {
            return [
                'success' => false,
                'message' => 'No se encontró dirección'
            ];
        }
        return (new ServiceData())->service($type, $person->number);
    }

    public function getAddressByPerson($person_id)
    {
        return PersonAddress::query()
            ->where('person_id', $person_id)
            ->get()
            ->transform(function ($row) {
                $location_id = [$row->department_id,$row->province_id,$row->district_id];
                return [
                    'id' => $row->id,
                    'location_id' => $location_id,
                    'address' => $row->address,
                    'code' => $row->establishment_code
                ];
            });
    }

    public function saveAddressPerson(Request $request)
    {
        $id = $request->input('id');
        $data = $request->all();
        if(is_array($data['location_id']) && count($data['location_id']) === 3) {
            $person['department_id'] = $data['location_id'][0];
            $person['province_id'] = $data['location_id'][1];
            $data['district_id'] = $data['location_id'][2];
        }else{
            return ['success' => false,'id' => 0];
        }

        $data['country_id'] = "PE";
        $record = PersonAddress::firstOrNew(['id' => $id]);
        $record->fill($data);
        $record->save();

        return [
            'success' => true,
            'id' => $record->id
        ];
    }

}
