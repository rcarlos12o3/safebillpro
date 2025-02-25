<?php

use Illuminate\Support\Facades\Route;

$current_hostname = app(Hyn\Tenancy\Contracts\CurrentHostname::class);

if($current_hostname) {
    Route::domain($current_hostname->fqdn)->group(function () {
        Route::middleware(['auth'])->group(function () {
            Route::prefix('qrchat')->group(function() {
                Route::get('/configuration', 'QrChatBuhoController@getConfig')->name('tenant.qrchat.configuration');
                Route::post('/configuration/update', 'QrChatBuhoController@updateConfig');
            });
        });
    });
};