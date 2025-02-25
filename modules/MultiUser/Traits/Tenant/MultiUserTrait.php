<?php

namespace Modules\MultiUser\Traits\Tenant;

use App\Models\System\Client;
use Hyn\Tenancy\Environment;
use Illuminate\Support\Facades\DB;
use Modules\MultiUser\Models\System\MultiUser;
use App\Models\Tenant\User;
use App\Models\Tenant\Company;
use Modules\MobileApp\Models\AppConfiguration;
use Exception;


trait MultiUserTrait
{

    /**
     * 
     * Retorna usuario autenticado
     *
     * @return User
     */
    public function getCurrentClient()
    {
        $website = $this->getTenantWebsite();

        return Client::currentClientByWebsite($website)->firstOrFail();
    }

    
    /**
     *
     * @param  int $client_id
     * @return Client
     */
    private function getClient($client_id)
    {
        return Client::filterDataMultiUser()->findOrFail($client_id);
    }


    /**
     * 
     * Obtener registro asociado al tenant desde la tabla website de system
     * 
     * @return Website
     */
    public function getTenantWebsite()
    {
        return app(Environment::class)->tenant();
    }


    /**
     *
     * @return User
     */
    private function getCurrentUser()
    {
        return auth()->user();
    }
    
    
    /**
     *
     * @param  int $origin_client_id
     * @param  User $current_client
     * @return array
     */
    public function getTableMultiUsers($origin_client_id, $current_client)
    {
        $current_user = $this->getCurrentUser();

        $multi_users = $this->getBaseMultiUsers($origin_client_id, $current_user->id);

        $this->addCurrentMultiUser($multi_users, $current_client);

        $this->addOriginMultiUsers($multi_users, $current_user);

        return $multi_users;
    }

        
    /**
     * 
     * Obtener empresas asociadas al usuario (registradas en el admin)
     *
     * @param  int $origin_client_id
     * @param  int $origin_user_id
     * @return array
     */
    public function getBaseMultiUsers($origin_client_id, $origin_user_id)
    {   
        return MultiUser::where('origin_client_id', $origin_client_id)
                        ->where('origin_user_id', $origin_user_id)
                        ->with(['destination_client'])
                        ->get()
                        ->transform(function($row){
                            return [
                                'id' => $row->id,
                                'is_destination' => true,
                                'client_full_name' => $row->destination_client->getFullName(),
                                'fqdn' => $row->destination_client->hostname->fqdn,
                            ];
                        });
    }

        
    /**
     * 
     * Agrega los datos de la empresa del usuario en sesion
     *
     * @param  collect $multi_users
     * @param  Client $current_client
     * @return void
     */
    public function addCurrentMultiUser(&$multi_users, $current_client)
    {
        $multi_users->push([
            'id' => null,
            'is_destination' => true,
            'client_full_name' => $current_client->getFullName(),
            'fqdn' => $current_client->hostname->fqdn,
        ]);
    }

    
    /**
     * 
     * Se valida si es usuario secundario, creado para auto login y agrega empresas asociadas al usuario origen
     *
     * @param  collect $multi_users
     * @param  User $current_client
     * @return void
     */
    public function addOriginMultiUsers(&$multi_users, $current_user)
    {
        if($current_user->is_multi_user)
        {
            // se obtiene registro de usuario origen del admin
            $origin_multi_user = MultiUser::with(['origin_client'])->findOrFail($current_user->multi_user_id);

            // se agregan datos de la empresa y usuario origen 
            // is_destination = false, para controlar el acceso a fk de cliente origen o destino (redireccion al usuario/cliente principal - origen)
            $multi_users->push([
                'id' => $origin_multi_user->id,
                'is_destination' => false,
                'client_full_name' => $origin_multi_user->origin_client->getFullName(),
                'fqdn' => $origin_multi_user->origin_client->hostname->fqdn,
            ]);


            // se obtienen las empresas asociadas al usuario origen
            $origin_users_access = $this->getBaseMultiUsers($origin_multi_user->origin_client_id, $origin_multi_user->origin_user_id);

            // se agregan empresas del usuario origen, a excepcion de la actual (addCurrentMultiUser)
            foreach ($origin_users_access as $row)
            {
                $exist_row = $multi_users->firstWhere('fqdn', $row['fqdn']);
                if(!$exist_row) $multi_users->push($row);
            }
        }
    }

    
    /**
     *
     * @param  bool $is_destination
     * @param  bool $is_destination
     * @param  int $client_id
     * @param  int $user_id
     * @return void
     */
    private function setClientUserId($is_destination, $multi_user, &$client_id, &$user_id)
    {
        if($is_destination)
        {
            $client_id = $multi_user->destination_client_id;
            $user_id = $multi_user->destination_user_id;   
        }
        else
        {
            $client_id = $multi_user->origin_client_id;
            $user_id = $multi_user->origin_user_id;   
        }
    }

    
    /**
     * 
     * @param  Client $client
     * @return void
     */
    public function setCurrentTenantConnection($client)
    {
        $tenancy = app(Environment::class);
        $tenancy->tenant($client->hostname->website);
    }

    
    /**
     *
     * @param  User $user
     * @return array
     */
    public function getDataDestinationClient($user)
    {
        $company = Company::active();

        return [
            'success' => true,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'establishment_id' => $user->establishment->id,
            'api_token' => $user->api_token,
            'ruc' => $company->number,
            'app_logo' => $company->app_logo,
            'company' => [
                'name' => $company->name,
                'address' => $user->establishment->department->description.', '.$user->establishment->province->description.', '.$user->establishment->district->description.', '.$user->establishment->address,
                'phone' => $user->establishment->telephone,
                'email' => $user->establishment->email
            ],
            'app_configuration' => optional(AppConfiguration::first())->getRowResource(),
        ];
    }

}