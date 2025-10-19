<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\BuildingManager\ProformaController;

Route::middleware(['auth:api', 'api'])->group(function () {
    Route::prefix('v1/building_manager')->group(function () {
        
        // Proforma routes
        Route::prefix('proforma')->group(function () {
            
            // Preview (calculate without saving)
            Route::post('/preview', [ProformaController::class, 'preview'])
                ->name('proforma.preview');
            
            // Create (calculate and save)
            Route::post('/', [ProformaController::class, 'store'])
                ->name('proforma.store');
            
            // Get proforma by ID
            Route::get('/{id}', [ProformaController::class, 'show'])
                ->name('proforma.show');
            
            // Export as HTML
            Route::get('/{id}/html', [ProformaController::class, 'html'])
                ->name('proforma.html');
        });
    });
});
