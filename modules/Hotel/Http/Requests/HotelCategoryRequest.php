<?php

namespace Modules\Hotel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HotelCategoryRequest extends FormRequest
{
	public function authorize()
	{
		return true;
	}

	public function rules()
	{
		$rules = [
            'description' => [
                'required', 
                'max:50', 
                Rule::unique('tenant.hotel_categories', 'description')
                    ->where(function ($query){
                        $query->where('establishment_id', $this->establishment_id);
                    })
                    ->ignore($this->id),
            ],
            'active' => 'required|boolean',
            'establishment_id' => 'required|exists:tenant.establishments,id'
        ];

        return $rules;
	}

	protected function prepareForValidation()
    {
        $user = auth()->user();

        if ($user->type !== 'admin') {
            $this->merge([
                'establishment_id' => $user->establishment_id,
            ]);
        }
    }
	
}
