<?php

namespace Modules\LevelAccess\Helpers;


use App\Models\Tenant\Configuration;
use Hyn\Tenancy\Contracts\CurrentHostname;
use Illuminate\Support\Facades\Cache;
use Hyn\Tenancy\Models\Hostname;


class SessionLifetimeHelper
{

    private const CACHE_KEY = 'tenant_session_lifetime';

    private const MINUTES_IN_HOURS = 60;

    
    /**
     *
     * @return void
     */
    public static function setTenantSessionLifetime()
    {
        if(!self::isTenantSessionLifetimeEnabled()) return;

        $hostname = self::getCurrentHostname();

        if($hostname)
        {
            $session_lifetime = self::getSessionLifetime($hostname);

            self::sessionLifetimeToMinutes($session_lifetime);

            config([
                'session.lifetime' => $session_lifetime
            ]);
        }
    }

    
    /**
     *
     * @return bool
     */
    public static function isTenantSessionLifetimeEnabled()
    {
        return config('configuration.tenant_session_lifetime_enabled');
    }

    
    /**
     *
     * @return CurrentHostname
     */
    public static function getCurrentHostname()
    {
        return app(CurrentHostname::class);
    }


    /**
     * 
     * Tiempo en minutos
     *
     * @param  float $session_lifetime
     * @return void
     */
    public static function sessionLifetimeToMinutes(&$session_lifetime)
    {
        $session_lifetime = $session_lifetime * self::MINUTES_IN_HOURS;
    }


    /**
     *
     * Tiempo en horas
     * 
     * @return float
     */
    public static function getSessionLifetime(Hostname $hostname)
    {
        if(self::existTenantSessionLifetime($hostname->fqdn)) return self::getTenantSessionLifetime($hostname->fqdn);

        $session_lifetime = Configuration::getRecordIndividualColumn('session_lifetime');

        self::saveTenantSessionLifetime($session_lifetime);

        return $session_lifetime;
    }

    
    /**
     *
     * @param  string $fqdn
     * @return float
     */
    public static function getTenantSessionLifetime($fqdn)
    {
        return Cache::get(self::CACHE_KEY."_{$fqdn}");
    }
       

    /**
     *
     * @param  string $fqdn
     * @return bool
     */
    public static function existTenantSessionLifetime($fqdn)
    {
        return Cache::has(self::CACHE_KEY."_{$fqdn}");
    }
       

    /**
     *
     * @param  float $session_lifetime
     * @return void
     */
    public static function saveTenantSessionLifetime($session_lifetime)
    {
        $hostname = self::getCurrentHostname();

        Cache::forever(self::CACHE_KEY."_{$hostname->fqdn}", $session_lifetime);
    }

}