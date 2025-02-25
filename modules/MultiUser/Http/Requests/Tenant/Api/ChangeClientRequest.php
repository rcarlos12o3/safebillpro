<?php

namespace Modules\MultiUser\Http\Requests\Tenant\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class ChangeClientRequest extends FormRequest
{
     
    public function authorize()
    {
        return true; 
    }
 
    public function rules()
    { 
        return [
            'fqdn' => [
                'required',
            ],
            'multi_user_id' => [
                'required',
            ],
            'is_destination' => [
                'required',
            ],
        ];
    }

}
