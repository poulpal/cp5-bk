<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Billing\ProformaInvoiceController;

// توجه: این فایل داخل گروه prefix('v1') لود می‌شود؛ لذا اینجا دیگر prefix('v1') نمی‌زنیم.

Route::post('proforma', [ProformaInvoiceController::class, 'store'])->name('proforma.store');
Route::get('proforma/{id}', [ProformaInvoiceController::class, 'show'])->name('proforma.show');
Route::get('proforma/{id}/html', [ProformaInvoiceController::class, 'html'])->name('proforma.html');
Route::get('proforma/{id}/pdf', [ProformaInvoiceController::class, 'pdf'])->name('proforma.pdf');
