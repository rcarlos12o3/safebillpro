<?php

use Illuminate\Support\Facades\Route;

$current_hostname = app(Hyn\Tenancy\Contracts\CurrentHostname::class);

if($current_hostname) {
    Route::domain($current_hostname->fqdn)->group(function () {
        Route::middleware(['auth'])->group(function () {
            Route::prefix('sire')->group(function() {
                Route::get('/configuration', 'SireController@getConfig')->name('tenant.sire.configuration');
                Route::post('/configuration/update', 'SireController@updateConfig');
                Route::get('/sale', 'SireController@index')->name('tenant.sire.sale')->middleware('redirect.level');
                Route::get('/purchase', 'SireController@index')->name('tenant.sire.purchase')->middleware('redirect.level');

                Route::get('/{type}/tables', 'SireController@tables');
                Route::get('/{type}/{period}/ticket', 'SireController@getTicket');
                Route::post('/{type}/query', 'SireController@queryTicket');
                Route::get('/{type}/{period}/accept', 'SireController@accept');
            });
        });
    });
};
