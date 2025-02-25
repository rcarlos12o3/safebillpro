<?php

namespace Modules\Sire\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Sire\Helpers\SireService;
use App\Models\Tenant\Company;

class SireController extends Controller
{
    /*
     * route get sire/sale
     * route get sire/purchase
     * view index
     */
    public function index()
    {
        return view('sire::index');
    }

    /*
     * route get sire/{type}/tables
     */
    public function tables($type)
    {
        $sire = new SireService();
        $periods = $sire->getPeriods($type);
        return $periods;
    }

    /*
     * route post sire/configuration/update
     * view company
     */
    public function updateConfig(Request $request)
    {
        $company = Company::first();
        $company->sire_client_id = $request->sire_client_id;
        $company->sire_client_secret = $request->sire_client_secret;
        $company->sire_username = $request->sire_username;
        $company->sire_password = $request->sire_password;
        $company->save();

        return [
            'success' => true,
            'message' => 'SIRE actualizado correctamente'
        ];
    }

    /*
     * route get sire/configuration
     * view company
     */
    public function getConfig()
    {
        $company = Company::select('sire_client_id','sire_client_secret','sire_username','sire_password')->first();

        return [
            'success' => true,
            'data' => $company,
        ];
    }

    /*
     * route get sire/{type}/{period}/ticket
     */
    public function getTicket($type, $period)
    {
        $sire = new SireService();
        return $sire->getTicket($type, $period);
    }

    /*
     * route get sire/{type}/query
     */
    public function queryTicket(Request $request, $type)
    {
        $sire = new SireService();
        $response = $sire->queryTicket($request->page,$request->period,$request->ticket, $type);
        return $response;
    }

    public function accept($type, $period)
    {
        $sire = new SireService();
        $response = $sire->sendAccept($period);
        return $response;
    }
}
