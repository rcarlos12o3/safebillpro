<?php

namespace Modules\MultiUser\Http\Controllers\Tenant;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Modules\MultiUser\Helpers\Tenant\AutoLoginHelper;


class AutoLoginController extends Controller
{

    /**
     * Redireccion a main
     *
     * @param  string $fqdn
     * @return mixed
     */
    public function autoLogin($fqdn, Request $request)
    {
        $helper = new AutoLoginHelper();

        $hostname = $helper->getCurrentHostname();
        
        $helper->validateFqdn($fqdn, $hostname->fqdn);

        return redirect("/{$request->previous_route}");
    }

}
