<?php

namespace Modules\QrApi\Http\Controllers;

use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\Models\Tenant\Configuration;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class QrApiController extends Controller
{
    use StorageDocument;

    public function getConfig()
    {
        $data = Configuration::select([
            'qr_api_url',
            'qr_api_apiKey',
            'qr_api_enable'
        ])->first();

        return [
            'qr_api_url_ws' => $data->qr_api_url,
            'qr_api_key_ws' => $data->qr_api_apiKey,
            'qr_api_enable_ws' => $data->qr_api_enable
        ];
    }

    public function updateConfig(Request $request)
    {
        $request->validate([
            'qr_api_url_ws' => 'required',
            'qr_api_key_ws' => 'required',
            'qr_api_enable_ws' => 'required'
        ]);
        $config = Configuration::first();
        $config->qr_api_url = $request->qr_api_url_ws;
        $config->qr_api_apiKey = $request->qr_api_key_ws;
        $config->qr_api_enable = $request->qr_api_enable_ws;
        $config->save();

        return [
            'success' => true,
            'message' => 'Datos actualizados correctamente'
        ];

    }

    public function encodeBase64(Request $request)
    {
        $request->validate([
            "filename_only" => "required",
            "extension_only" => "required"
        ]);

        $filename = $request->filename_only;
        $extension = $request->extension_only;

        // Se tiene que buscar el archivo dentro de storage
        $content_file = $this->getStorage($filename, 'pdf');
       
        return base64_encode($content_file);
    }
}
