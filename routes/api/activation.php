<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ActivationTestController;

Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    Route::post('subscriptions/activate/test', [ActivationTestController::class, 'activate'])
        ->name('v1.subscriptions.activate.test');
});
