<?php

namespace Modules\MultiUser\Http\Controllers\Tenant;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Modules\MultiUser\Traits\Tenant\MultiUserTrait;
use Modules\MultiUser\Helpers\Tenant\AutoLoginHelper;


class MultiUserController extends Controller
{

    use MultiUserTrait;

    /**
     *
     * @param  Request $request
     * @return mixed
     */
    public function changeClient(Request $request)
    {
        $is_destination = $request->is_destination == "true";
        $helper = new AutoLoginHelper();

        $multi_user = $helper->getMultiUser($request->multi_user_id);

        $client_id = $is_destination ? $multi_user->destination_client_id : $multi_user->origin_client_id;

        $client = $this->getClient($client_id);

        $fqdn = $client->hostname->fqdn;
        $previous_route = Request::create(url()->previous())->path();

        $helper->saveLoginRequest($fqdn, [
            'fqdn' => $fqdn,
            'multi_user_id' => $multi_user->id,
            'is_destination' => $is_destination
        ]);

        return redirect()->to($helper->redirectUrl($fqdn, $previous_route));
    }

   
    /**
     *
     * @return array
     */
    public function records()
    {
        $current_client = $this->getCurrentClient();

        $origin_client_id = $current_client->id;

        return $this->getTableMultiUsers($origin_client_id, $current_client);
    }

}
