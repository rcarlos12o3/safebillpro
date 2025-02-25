<?php

namespace Modules\PseService\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\PseService\Http\Gior\Endpoints as GiorEndpoints;
use Modules\PseService\Http\Gior\Errors as GiorErrors;
use Modules\PseService\Http\Gior\Service as GiorService;
use Modules\Company\Models\Company;

class PseServiceController extends Controller
{
    public function index()
    {
        $giorService = new GiorService();
        $getToken = $giorService->getToken();
        $res = [
            'endpoint' => GiorEndpoints::TOKEN,
            'error' => GiorErrors::getMessage('200'),
            'getToken' => $getToken,
            'validateSend' => $this->validateSend()
        ];

        // dd($res);
        return view('pseservice::index');
    }

    public function validateSend() {
        $company = Company::first();

        return $company->soap_send_id == 03 ? true : false;
    }
}
