<?php

namespace Modules\MultiUser\Http\Controllers\Tenant\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Modules\MultiUser\Traits\Tenant\MultiUserTrait;
use Modules\MultiUser\Helpers\Tenant\AutoLoginHelper;
use Modules\MultiUser\Http\Controllers\Tenant\MultiUserController as WebMultiUserController;
use Modules\MultiUser\Http\Requests\Tenant\Api\ChangeClientRequest;


class MultiUserController extends Controller
{

    use MultiUserTrait;


    /**
     *
     * @param  Request $request
     * @return mixed
     */
    public function changeClient(ChangeClientRequest $request)
    {
        $helper = new AutoLoginHelper();
        $client_id = null;
        $user_id = null;

        $multi_user = $helper->getMultiUser($request->multi_user_id);

        $this->setClientUserId($request->is_destination, $multi_user, $client_id, $user_id);

        $client = $this->getClient($client_id);

        $helper->validateFqdn($request->fqdn, $client->hostname->fqdn);

        $this->setCurrentTenantConnection($client);

        $user = $helper->findUser($user_id);

        if(is_null($user->api_token)) return $this->generalResponse(false, 'El usuario en la empresa destino no tiene un api token definido.');

        return $this->getDataDestinationClient($user);
    }
    
   
    /**
     *
     * @return array
     */
    public function records()
    {
        return app(WebMultiUserController::class)->records();
    }

}
