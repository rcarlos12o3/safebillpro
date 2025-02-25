<?php

namespace Modules\MultiUser\Http\Requests\System;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class MultiUserRequest extends FormRequest
{
     
    public function authorize()
    {
        return true; 
    }
 
    public function rules()
    { 
        return [
            'destination_client_id' => [
                'required',
            ],
            'composed_id' => [
                'required',
            ],
        ];

    }
}
