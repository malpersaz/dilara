<?php

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Webkul\Payment\Http\Controllers\KuveytTurkController;

Route::group(['middleware' => ['web']], function () {
    Route::controller(KuveytTurkController::class)
        ->prefix('kuveytturk')
        ->group(function () {
            Route::get('redirect', 'redirect')->name('kuveytturk.redirect');

            Route::post('process', 'process')->name('kuveytturk.process');

            Route::post('callback', 'callback')
                ->withoutMiddleware(VerifyCsrfToken::class)
                ->name('kuveytturk.callback');
        });
});
