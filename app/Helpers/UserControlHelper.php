<?php

namespace App\Helpers;

use App\Models\Tenant\User;
use App\Traits\LockedEmissionTrait;
use Exception;


class UserControlHelper
{

    use LockedEmissionTrait;
    

    /**
     * 
     * Limite usuarios
     *
     * @return array
     */
    public function exceedLimitUsers()
    {
        if($this->getConfigurationColumn('locked_users'))
        {
            $plan = $this->getClientPlan(['id', 'limit_users']);

            if(!$plan->isUnlimitedUsers())
            {
                $quantity = User::getQuantityActive();

                if($quantity >= $plan->limit_users)
                {
                    return $this->getResponse(true, 'Ha superado el límite permitido para crear/activar usuarios.');
                }
            }
        }

        return $this->getResponse(false);
    }

    
    /**
     * 
     * Validar limite de usuarios
     *
     * @return void
     */
    public function checkLimitUsers()
    {
        $exceed_limit_users = $this->exceedLimitUsers();

        if($exceed_limit_users['success']) $this->throwException($exceed_limit_users['message']);
    }

    
    /**
     * 
     *
     * @return void
     */
    public static function checkActiveUser()
    {
        $user = auth()->user();

        if($user)
        {
            if(!$user->isActive()) abort(403);
        }
    }

}