<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$hostname = app(Hyn\Tenancy\Contracts\CurrentHostname::class);

if ($hostname) {
    Route::domain($hostname->fqdn)->group(function () {

        Route::middleware(['auth:api', 'locked.tenant'])->group(function () {

            Route::prefix('dispatch-carrier')
                ->group(function () {
                Route::post('/', 'Api\DispatchCarrierController@store');
            });

            Route::prefix('dispatch-addresses')->group(function () {
                Route::get('/tables', 'DispatchAddressController@tables');
                Route::post('/save', 'DispatchAddressController@saveAddressPerson');
                Route::get('/get_by_person/{sender_id}', 'DispatchAddressController@getAddressByPerson');
            });
        });
    });
};
