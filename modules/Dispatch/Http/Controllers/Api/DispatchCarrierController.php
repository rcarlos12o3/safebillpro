<?php

namespace Modules\Dispatch\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\CoreFacturalo\Facturalo;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Tenant\Dispatch;
use Modules\ApiPeruDev\Http\Controllers\ServiceDispatchController;


class DispatchCarrierController extends Controller
{
    public function __construct()
    {
        $this->middleware('input.request:dispatch,api', ['only' => ['store']]);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $fact = DB::connection('tenant')->transaction(function () use ($request) {
            $facturalo = new Facturalo();
            $facturalo->save($request->all());
            $document = $facturalo->getDocument();
            $data = (new ServiceDispatchController())->getData($document->id);
            $facturalo->setXmlUnsigned((new ServiceDispatchController())->createXmlUnsigned($data));
            $facturalo->signXmlUnsigned();
            $facturalo->createPdf();
            return $facturalo;
        });

        $document = $fact->getDocument();

        return [
            'success' => true,
            'data' => [
                'number' => $document->number_full,
                'filename' => $document->filename,
                'external_id' => $document->external_id,
            ],
        ];
    }
}