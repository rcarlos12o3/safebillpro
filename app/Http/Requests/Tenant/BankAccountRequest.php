<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class BankAccountRequest extends FormRequest
{
	public function authorize()
	{
		return true;
	}

	public function rules()
	{
		$id = $this->input('id');
		$select_establishment_bank_account = $this->input('select_establishment_bank_account') ?? false;
		
		return [
			'bank_id' => [
				'required',
			],
			'description' => [
				'required',
			],
			'number' => [
				'required',
			],
			'currency_type_id' => [
				'required',
			],
			'initial_balance' => [
				'required',
				'numeric',
				'min:0'
			],
			'show_in_documents' => [
				'required',
				'boolean'
			],
			'establishment_id' => [
				$select_establishment_bank_account ? 'required' : '',
			],
		];
	}

	public function messages()
	{
		return [
			// 'initial_balance.gte' => 'El saldo inicial debe ser mayor o igual que 0.',
		];
	}
}
