<?php

namespace Modules\Dispatch\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DispatchAddressRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->input('id');
        $address = $this->input('address');
        $person_id = $this->input('person_id');

        $unique_rule = Rule::unique('tenant.dispatch_addresses')
                            ->where(function ($query) use($address, $person_id) {
                                return $query->where('address', $address)
                                            ->where('person_id', $person_id);
                            })
                            ->ignore($id);

        return [
            'location_id' => [
                'required',
            ],
            'establishment_code' => [
                'required',
            ],
            'address' => [
                'required',
                $unique_rule
            ],
            'person_id' => [
                'required',
                $unique_rule
            ],
        ];
    }


    public function messages()
    {
        return [
            'person_id.required' => 'El campo cliente es obligatorio.',
            'person_id.unique' => 'cliente ya ha sido registrado.',
        ];
    }

}
