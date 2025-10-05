<?php

use Illuminate\Support\Facades\Route;

Route::prefix('gateway')->group(function () {
    Route::get('/test', [\App\Http\Controllers\GatewayTestController::class, 'index']);
    Route::post('/gateWayTest', [\App\Http\Controllers\GatewayTestController::class, 'pay'])->name('gateWayTest');
    Route::any('/testCallback', [\App\Http\Controllers\GatewayTestController::class, 'testCallback'])->name('testCallback');
});
