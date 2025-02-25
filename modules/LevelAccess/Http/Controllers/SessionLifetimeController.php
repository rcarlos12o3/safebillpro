<?php

namespace Modules\LevelAccess\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Configuration;
use Modules\LevelAccess\Helpers\SessionLifetimeHelper;
use Modules\LevelAccess\Http\Requests\SessionLifetimeRequest;


class SessionLifetimeController extends Controller
{
    
    /**
     * 
     * @return array
     */
    public function data()
    {
        return [
            'session_lifetime' => SessionLifetimeHelper::getSessionLifetime(SessionLifetimeHelper::getCurrentHostname()),
            'is_tenant_session_lifetime_enabled' => SessionLifetimeHelper::isTenantSessionLifetimeEnabled()
        ];
    }


    /**
     *
     * @param  SessionLifetimeRequest $request
     * @return array
     */
    public function store(SessionLifetimeRequest $request)
    {
        $configuration = Configuration::firstOrFail();
        $configuration->session_lifetime = $request->session_lifetime;
        $configuration->save();

        SessionLifetimeHelper::saveTenantSessionLifetime($configuration->session_lifetime);

        return $this->generalResponse(true, 'Configuración actualizada');
    }

}
