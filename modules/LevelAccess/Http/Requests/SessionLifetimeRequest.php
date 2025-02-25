<?php

namespace Modules\LevelAccess\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SessionLifetimeRequest extends FormRequest
{

    public function rules()
    {
        return [
            'session_lifetime' => [
                'required',
                'numeric',
                'gte:1',
                'lte:24',
            ],
        ];
    }

    
    public function authorize()
    {
        return true;
    }

    
    public function messages()
    {
        return [
            'session_lifetime.gte' => 'La duración de la sesión debe ser mayor o igual que 1.',
            'session_lifetime.lte' => 'La duración de la sesión debe ser menor o igual que 24.',
        ];
    }

}
