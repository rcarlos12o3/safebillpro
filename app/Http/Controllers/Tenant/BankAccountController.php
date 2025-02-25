<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\BankAccountRequest;
use App\Http\Resources\Tenant\BankAccountCollection;
use App\Http\Resources\Tenant\BankAccountResource;
use App\Models\Tenant\Bank;
use App\Models\Tenant\BankAccount;
use App\Models\Tenant\Catalogs\CurrencyType;
use App\Models\Tenant\{
    Establishment,
    Configuration
};
use Exception;


class BankAccountController extends Controller
{
    public function index()
    {
        return view('tenant.bank_accounts.index');
    }

    public function records()
    {
        $records = BankAccount::all();

        return new BankAccountCollection($records);
    }

    public function create()
    {
        return view('tenant.bank_accounts.index');
    }

    public function tables()
    {
        $banks = Bank::all();
        $currency_types = CurrencyType::whereActive()->get();
        $establishments = Establishment::filterDataForTables()->get();
        $select_establishment_bank_account = Configuration::getRecordIndividualColumn('select_establishment_bank_account');

        return compact('banks', 'currency_types', 'establishments', 'select_establishment_bank_account');
    }


    public function record($id)
    {
        $record = new BankAccountResource(BankAccount::findOrFail($id));

        return $record;
    }

    public function store(BankAccountRequest $request)
    {
        $id = $request->input('id');
        $bank_account = BankAccount::firstOrNew(['id' => $id]);
        $bank_account->fill($request->all());
        $bank_account->save();

        return [
            'success' => true,
            'message' => ($id)?'Cuenta bancaria editada con éxito':'Cuenta bancaria registrada con éxito'
        ];
    }

    public function destroy($id)
    {
        try {
            
            $bank_account = BankAccount::findOrFail($id);
            $bank_account->delete(); 

            return [
                'success' => true,
                'message' => 'Cuenta bancaria eliminada con éxito'
            ];

        } catch (Exception $e) {

            return ($e->getCode() == '23000') ? ['success' => false,'message' => 'La Cuenta bancaria esta siendo usada por otros registros, no puede eliminar'] : ['success' => false,'message' => 'Error inesperado, no se pudo eliminar la cuenta bancaria'];

        } 
    }
}