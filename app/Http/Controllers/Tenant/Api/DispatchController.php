<?php

namespace App\Http\Controllers\Tenant\Api;

use App\CoreFacturalo\Facturalo;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Dispatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\ApiPeruDev\Http\Controllers\ServiceDispatchController;
use App\Models\Tenant\Establishment;
use Modules\Dispatch\Models\Driver;
use Modules\Dispatch\Models\Transport;
use App\Models\Tenant\Catalogs\{
    IdentityDocumentType,
    TransferReasonType,
    TransportModeType,
    UnitType
};
use Modules\Dispatch\Models\OriginAddress;

class DispatchController extends Controller
{
    public function __construct()
    {
        $this->middleware('input.request:dispatch,api', ['only' => ['store']]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'delivery.address' => 'required|max:100',
            'origin.address' => 'required|max:100',
        ]);

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

    public function send(Request $request)
    {
        $external_id = $request->input('external_id');
        $record = Dispatch::query()
            ->where('external_id', $external_id)
            ->first();
        if (!$record) {
            return [
                'success' => false,
                'message' => 'El external id es incorrecto'
            ];
        }
        return ((new ServiceDispatchController())->send($external_id));
    }

    public function statusTicket(Request $request)
    {
        $external_id = $request->input('external_id');
        $record = Dispatch::query()
            ->where('external_id', $external_id)
            ->first();
        if (!$record) {
            return [
                'success' => false,
                'message' => 'El external id es incorrecto'
            ];
        }
        $res = ((new ServiceDispatchController())->statusTicket($external_id));
        (new Facturalo())->createPdf($record, 'dispatch', 'a4');
        return $res;

    }

    /**
    * Tables
    * @param  Request $request
    * @return \Illuminate\Http\Response
    */
    public function tables(Request $request) {
        $transferReasonTypes = TransferReasonType::whereActive()->get();
        $transportModeTypes = TransportModeType::whereActive()->get();
        $unitTypes = UnitType::whereActive()->get();
        $establishments = Establishment::all();
        $origin_addresses = OriginAddress::all();
        $dispatchers = app(\Modules\Dispatch\Http\Controllers\DispatcherController::class)->getOptions();
        $transports = Transport::query()
            ->where('is_active', true)
            ->get()
            ->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'plate_number' => $row->plate_number,
                    'model' => $row->model,
                    'brand' => $row->brand,
                    'is_default' => $row->is_default
                ];
            });
        $drivers = Driver::query()
            ->where('is_active', true)
            ->get()
            ->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'identity_document_type_id' => $row->identity_document_type_id,
                    'number' => $row->number,
                    'name' => $row->name,
                    'license' => $row->license,
                    'telephone' => $row->telephone,
                ];
            });

        return compact('establishments', 'origin_addresses' , 'transportModeTypes', 'transferReasonTypes', 'unitTypes', 'dispatchers','transports','drivers');
    }
}
