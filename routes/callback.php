<?php

use App\Http\Controllers\CallbackController;
use Illuminate\Support\Facades\Route;

Route::get('/paymentStatus', [CallbackController::class, 'showPaymentStatus'])->name('paymentStatus');

Route::get('/paymentRedirect', [CallbackController::class, 'paymentRedirect'])->name('paymentRedirect');

Route::prefix('callback')->name('callback.')->group(function () {
    // Route::get('/payir', [CallbackController::class, 'payir'])->name('payir');
    Route::get('/test', [CallbackController::class, 'test'])->name('test');
    Route::post('/sepehr', [CallbackController::class, 'sepehr'])->name('sepehr');
    Route::post('/sep', [CallbackController::class, 'sep'])->name('sep');
    Route::any('/pasargad', [CallbackController::class, 'pasargad'])->name('pasargad');
    Route::any('inopay', [CallbackController::class, 'inopay'])->name('inopay');
});
