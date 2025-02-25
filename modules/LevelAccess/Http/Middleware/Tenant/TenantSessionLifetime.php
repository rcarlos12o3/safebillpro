<?php

namespace Modules\LevelAccess\Http\Middleware\Tenant;

use Closure;
use Modules\LevelAccess\Helpers\SessionLifetimeHelper;


class TenantSessionLifetime
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

}
