<?php

$hostname = app(Hyn\Tenancy\Contracts\CurrentHostname::class);

if ($hostname)
{
    Route::domain($hostname->fqdn)->group(function () {

        Route::middleware(['auth:api', 'locked.tenant'])->group(function () {

            Route::prefix('multi-users')->group(function () {
                
                Route::get('records', 'Tenant\Api\MultiUserController@records');
                Route::post('change-client', 'Tenant\Api\MultiUserController@changeClient');
                
            });
            
        });
    });
}
