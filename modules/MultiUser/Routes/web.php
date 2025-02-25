<?php

use Illuminate\Support\Facades\Route;

$hostname = app(Hyn\Tenancy\Contracts\CurrentHostname::class);

if ($hostname)
{
    Route::domain($hostname->fqdn)->group(function () {

        Route::middleware([ 'auth', 'redirect.module', 'locked.tenant'])->group(function () {

            Route::prefix('multi-users')->group(function () {

                Route::get('records', 'Tenant\MultiUserController@records');
                Route::post('', 'Tenant\MultiUserController@changeClient');

            });

        });


        Route::middleware(['tenant.auto.login', 'auth', 'locked.tenant'])->group(function () {
            Route::get('auto-login/{fqdn}', 'Tenant\AutoLoginController@autoLogin');
        });

    });

}
else
{
    $prefix = env('PREFIX_URL',null);
    $prefix = !empty($prefix) ? $prefix.'.' : '';
    $app_url = $prefix.env('APP_URL_BASE');

    Route::domain($app_url)->group(function () {

        Route::middleware('auth:admin')->group(function () {

            Route::prefix('multi-users')->group(function () {

                Route::get('', 'System\MultiUserController@index')->name('system.multi-users.index');
                Route::get('columns', 'System\MultiUserController@columns');
                Route::get('records', 'System\MultiUserController@records');
                Route::get('tables', 'System\MultiUserController@tables');
                Route::post('', 'System\MultiUserController@store');
                Route::delete('{id}', 'System\MultiUserController@delete');

            });

        });
    });
}
