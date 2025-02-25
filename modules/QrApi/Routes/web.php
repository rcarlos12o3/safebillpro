<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

$current_hostname = app(Hyn\Tenancy\Contracts\CurrentHostname::class);

if($current_hostname) {
    Route::domain($current_hostname->fqdn)->group(function () {
        Route::middleware(['auth'])->group(function () {
            Route::prefix('qrapi')->group(function() {
                Route::get('/configuration', 'QrApiController@getConfig')->name('tenant.qrchat.configuration');
                Route::post('/configuration/update', 'QrApiController@updateConfig');

                Route::post('/encode', 'QrApiController@encodeBase64');
            });
        });
    });
};