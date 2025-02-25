<?php

namespace Modules\QrChatBuho\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Models\Tenant\Configuration;

class QrChatBuhoController extends Controller
{

    public function getConfig()
    {
        $data = Configuration::select([
            'qrchat_url',
            'qrchat_app_key',
            // 'qrchat_auth_key',
            'qrchat_enable'])->first();

        return $data;
    }

    public function updateConfig(Request $request)
    {
        $request->validate([
            'qrchat_url' => 'required',
            'qrchat_app_key' => 'required',
            // 'qrchat_auth_key' => 'required'
        ]);
        $config = Configuration::first();
        $config->qrchat_url = $request->qrchat_url;
        $config->qrchat_app_key = $request->qrchat_app_key;
        // $config->qrchat_auth_key = $request->qrchat_auth_key;
        $config->qrchat_enable = $request->qrchat_enable;
        $config->save();

        return [
            'success' => true,
            'message' => 'Datos actualizados correctamente'
        ];
    }
}
