<?php

namespace Modules\MultiUser\Helpers\Tenant;

use Illuminate\Support\Facades\Auth;
use Hyn\Tenancy\Contracts\CurrentHostname;
use Illuminate\Support\Facades\Cache;
use Modules\MultiUser\Models\System\MultiUser;
use App\Models\Tenant\User;
use Exception;


class AutoLoginHelper
{
    
    private const CACHE_KEY = 'auto_login';
    private const CACHE_TIME = 1;

    
    /**
     * 
     * Iniciar proceso de validacion e inicio de sesion automatico
     *
     * @return void
     */
    public function startProcess()
    {
        $hostname = $this->getCurrentHostname();

        if($hostname)
        {
            if($this->existLoginRequest($hostname->fqdn))
            {
                $login_data = $this->getLoginData($hostname->fqdn);

                $this->validateFqdn($login_data->fqdn, $hostname->fqdn);
    
                $this->deleteLoginRequest($hostname->fqdn);

                $this->runLogin($login_data);

                return;
            }
            
            $this->throwException("Acceso automático inválido, solicitud incorrecta: {$hostname->fqdn}");
        }
    }


    /**
     *
     * @param  object $login_data
     * @return void
     */
    private function runLogin($login_data)
    {
        $multi_user = $this->getMultiUser($login_data->multi_user_id);

        $user_id = $login_data->is_destination ? $multi_user->destination_user_id : $multi_user->origin_user_id;

        $user = $this->findUser($user_id);

        $this->loginById($user);

        // \Log::info(
        //     "status: ". Auth::check()
        //     ." name: ".auth()->user()->name
        //     ." updated_at: ".auth()->user()->updated_at
        // );
    }

    
    /**
     *
     * @param  User $user
     * @return void
     */
    public function loginById(User $user)
    {
        Auth::loginUsingId($user->id);
    }


    /**
     *
     * @return CurrentHostname
     */
    public function getCurrentHostname()
    {
        return app(CurrentHostname::class);
    }

        
    /**
     *
     * @param  string $fqdn
     * @return bool
     */
    private function existLoginRequest($fqdn)
    {
        return Cache::has(self::CACHE_KEY."_{$fqdn}");
    }
       
        
    /**
     *
     * @param  array $data
     * @return void
     */
    public function saveLoginRequest($fqdn, $data)
    {
        Cache::put(self::CACHE_KEY."_{$fqdn}", json_encode($data), self::CACHE_TIME);
    } 

    
    /**
     *
     * @param  string $fqdn
     * @param  string $previous_route
     * @return string
     */
    public function redirectUrl($fqdn, $previous_route)
    {
        $protocol = config('tenant.force_https') ? 'https' : 'http';
        
        return "{$protocol}://".$fqdn."/auto-login/{$fqdn}?previous_route={$previous_route}";
    }


    /**
     *
     * @param  string $fqdn
     * @return object
     */
    private function getLoginData($fqdn)
    {
        return json_decode(Cache::get(self::CACHE_KEY."_{$fqdn}"));
    }
    

    /**
     *
     * @param  string $fqdn
     * @return void
     */
    private function deleteLoginRequest($fqdn)
    {
        Cache::forget(self::CACHE_KEY."_{$fqdn}");
    }
        
    
    /**
     * @param  int $user_id
     * @return User
     */
    public function findUser($user_id)
    {
        return User::whereFilterWithOutRelations()->findOrFail($user_id);
    }

    
    /**
     *
     * @param  int $multi_user_id
     * @return MultiUser
     */
    public function getMultiUser($multi_user_id)
    {
        return MultiUser::select([
                                'id', 
                                'destination_user_id', 
                                'destination_client_id',
                                'origin_client_id',
                                'origin_user_id'
                            ])
                            ->findOrFail($multi_user_id);
    }

    
    /**
     *
     * @param  string $input_fqdn
     * @param  string $current_fqdn
     * @return void
     */
    public function validateFqdn($input_fqdn, $current_fqdn)
    {
        if($input_fqdn !== $current_fqdn) $this->throwException("El fqdn actual es diferente al obtenido para iniciar sesión: {$input_fqdn} - {$current_fqdn}");
    }

    
    /**
     *
     * @param  string $message
     * @return void
     */
    public function throwException($message)
    {
        throw new Exception($message);
    }


}