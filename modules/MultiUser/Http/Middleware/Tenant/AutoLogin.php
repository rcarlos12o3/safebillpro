<?php

namespace Modules\MultiUser\Http\Middleware\Tenant;

use Closure;
use Modules\MultiUser\Helpers\Tenant\AutoLoginHelper;


class AutoLogin
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
        if($request->ajax()) return $next($request);

        (new AutoLoginHelper)->startProcess();

        return $next($request);
    }

}
