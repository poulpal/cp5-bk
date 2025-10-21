<?php

use App\Http\Controllers\CallbackController;

use App\Http\Controllers\Api\V1\Public\PayChargeController;
use App\Http\Controllers\Api\V1\Public\ReserveController;
use App\Http\Controllers\Api\V1\Public\PlanController;
use App\Http\Controllers\Api\V1\Public\SurveyController;
use App\Http\Controllers\Api\V1\Public\CharityController;
use App\Http\Controllers\Api\V1\Public\ModuleController;
use App\Http\Controllers\Api\V1\Public\AnnouncementController;
use App\Http\Controllers\Api\V1\Public\VersionController;
use App\Http\Controllers\Api\V1\Public\BannerController;
use App\Http\Controllers\Api\V1\Auth\BusinessRegisterController;

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\User\ProfileController as UserProfileController;
use App\Http\Controllers\Api\V1\User\BuildingUnitController as UserBuildingUnitController;
use App\Http\Controllers\Api\V1\User\InvoiceController as UserInvoiceController;
use App\Http\Controllers\Api\V1\User\TransactionController as UserTransactionController;
use App\Http\Controllers\Api\V1\User\ContactController as UserContactController;
use App\Http\Controllers\Api\V1\User\AnnouncementController as UserAnnouncementController;
use App\Http\Controllers\Api\V1\User\TicketController as UserTicketController;
use App\Http\Controllers\Api\V1\User\ReserveController as UserReserveController;
use App\Http\Controllers\Api\V1\User\PollController as UserPollController;
use App\Http\Controllers\Api\V1\User\RentalController as UserRentalController;
use App\Http\Controllers\Api\V1\User\WalletController as UserWalletController;

use App\Http\Controllers\Api\V1\Business\ProfileController as BusinessProfileController;
use App\Http\Controllers\Api\V1\Business\AnnouncementController as BusinessAnnouncementController;
use App\Http\Controllers\Api\V1\Business\TicketController as BusinessTicketController;
use App\Http\Controllers\Api\V1\Business\ReserveController as BusinessReserveController;

use App\Http\Controllers\Api\V1\BuildingManager\ProfileController as BuildingManagerProfileController;
use App\Http\Controllers\Api\V1\BuildingManager\AnnouncementController as BuildingManagerAnnouncementController;
use App\Http\Controllers\Api\V1\BuildingManager\UnitController as BuildingManagerUnitController;
use App\Http\Controllers\Api\V1\BuildingManager\InvoiceController as BuildingManagerInvoiceController;
use App\Http\Controllers\Api\V1\BuildingManager\DebtController as BuildingManagerDebtController;
use App\Http\Controllers\Api\V1\BuildingManager\TransactionController as BuildingManagerTransactionController;
use App\Http\Controllers\Api\V1\BuildingManager\ReserveController as BuildingManagerReserveController;
use App\Http\Controllers\Api\V1\BuildingManager\PollController as BuildingManagerPollController;
use App\Http\Controllers\Api\V1\BuildingManager\CharityController as BuildingManagerCharityController;
use App\Http\Controllers\Api\V1\BuildingManager\PayChargeController as BuildingManagerPayChargeController;
use App\Http\Controllers\Api\V1\BuildingManager\RentController as BuildingManagerRentController;
use App\Http\Controllers\Api\V1\BuildingManager\WalletController as BuildingManagerWalletController;
use App\Http\Controllers\Api\V1\BuildingManager\ModuleController as BuildingManagerModuleController;
use App\Http\Controllers\Api\V1\BuildingManager\FactorController as BuildingManagerFactorController;
use App\Http\Controllers\Api\V1\BuildingManager\DebtTypeController as BuildingManagerDebtTypeController;
use App\Http\Controllers\Api\V1\BuildingManager\ProformaController;
use App\Http\Controllers\Api\V1\BuildingManager\FeatureController;
use App\Http\Controllers\Api\V1\BuildingManager\LateFineController;

use App\Http\Controllers\Api\V1\Business\WalletController as BusinessWalletController;

Route::name('v1.')->group(function () {

    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('send_otp', [LoginController::class, 'sendOtp'])->name('sendOtp');
        Route::post('verify', [LoginController::class, 'verify'])->name('verify');
        Route::post('logout', [LoginController::class, 'logout'])->name('logout');
        Route::post('business_register', BusinessRegisterController::class)->name('businessRegister');
    });

    Route::prefix('public')->name('public.')->group(function () {

        Route::prefix('pay_charge')->name('pay_charge.')->group(function () {
            Route::post('get_unit', [PayChargeController::class, 'getUnit'])->name('getUnit');
            Route::post('get_invoices', [PayChargeController::class, 'getInvoices'])->name('getInvoices');
            Route::post('pay', [PayChargeController::class, 'pay'])->name('pay');
        });

        Route::prefix('reserve')->name('reserve.')->group(function () {
            Route::post('get_reservables', [ReserveController::class, 'getReservables'])->name('getReservables');
            Route::post('get_times', [ReserveController::class, 'getTimes'])->name('getTimes');
            Route::post('reserve', [ReserveController::class, 'reserve'])->name('reserve');
        });

        Route::get('plans', [PlanController::class, 'index'])->name('plans.index');
        Route::get('surveys', [SurveyController::class, 'index'])->name('surveys.index');

        Route::prefix('charity')->name('charity.')->group(function () {
            Route::get('/', [CharityController::class, 'index'])->name('index');
            Route::post('pay', [CharityController::class, 'pay'])->name('pay');
        });
        Route::apiResource('modules', ModuleController::class)->only(['index']);

        Route::get('version', [VersionController::class, 'index'])->name('version')
            ->middleware(['versioning']);

        Route::apiResource('announcements', AnnouncementController::class)->only(['index', 'show']);
        Route::apiResource('banners', BannerController::class)->only(['index', 'show']);
    });

    Route::middleware(['auth:api'])->name('user.')->prefix('user')->group(function () {

        Route::get('profile', [UserProfileController::class, 'show'])->name('profile');
        Route::post('profile/update', [UserProfileController::class, 'update'])->name('profile.update');

        Route::get('building_units', [UserBuildingUnitController::class, 'index'])->name('building_units.index');

        Route::get('invoices', [UserInvoiceController::class, 'index'])->name('invoices.index');
        Route::get('invoices/{invoice}', [UserInvoiceController::class, 'show'])->name('invoices.show');
        Route::get('invoices/{invoice}/pdf', [UserInvoiceController::class, 'showPdf'])->name('invoices.pdf');

        Route::get('transactions', [UserTransactionController::class, 'index'])->name('transactions.index');
        Route::get('transactions/{transaction}', [UserTransactionController::class, 'show'])->name('transactions.show');

        Route::post('contact', [UserContactController::class, 'send'])->name('contact.send');

        Route::apiResource('announcements', UserAnnouncementController::class)->only(['index', 'show']);

        Route::prefix('tickets')->name('tickets.')->group(function () {
            Route::get('/', [UserTicketController::class, 'index'])->name('index');
            Route::post('/', [UserTicketController::class, 'store'])->name('store');
            Route::get('{ticket}', [UserTicketController::class, 'show'])->name('show');
            Route::post('{ticket}/message', [UserTicketController::class, 'message'])->name('message');
        });

        Route::prefix('reserve')->name('reserve.')->group(function () {
            Route::get('reservables', [UserReserveController::class, 'reservables'])->name('reservables');
            Route::post('reserve', [UserReserveController::class, 'reserve'])->name('reserve');
            Route::post('cancel', [UserReserveController::class, 'cancel'])->name('cancel');
            Route::post('reply', [UserReserveController::class, 'reply'])->name('reply');
        });

        Route::prefix('polls')->name('polls.')->group(function () {
            Route::get('/', [UserPollController::class, 'index'])->name('index');
            Route::get('{poll}', [UserPollController::class, 'show'])->name('show');
            Route::post('{poll}/vote', [UserPollController::class, 'vote'])->name('vote');
        });

        Route::prefix('rent')->name('rent.')->group(function () {
            Route::get('contracts', [UserRentalController::class, 'contracts'])->name('contracts');
            Route::get('contracts/{contract}', [UserRentalController::class, 'show'])->name('contracts.show');
            Route::get('contracts/{contract}/invoices', [UserRentalController::class, 'invoices'])->name('contracts.invoices');
            Route::post('contracts/{contract}/pay', [UserRentalController::class, 'pay'])->name('contracts.pay');
        });

        Route::prefix('wallet')->name('wallet.')->group(function () {
            Route::get('/', [UserWalletController::class, 'index'])->name('index');
            Route::post('charge', [UserWalletController::class, 'charge'])->name('charge');
            Route::post('withdraw', [UserWalletController::class, 'withdraw'])->name('withdraw');
        });

    });

    Route::middleware(['auth:api', 'business'])->name('business.')->prefix('business')->group(function () {

        Route::get('profile', [BusinessProfileController::class, 'show'])->name('profile.show');
        Route::post('profile', [BusinessProfileController::class, 'update'])->name('profile.update');

        Route::apiResource('announcements', BusinessAnnouncementController::class)->only(['index', 'show', 'store', 'update', 'destroy']);

        Route::prefix('tickets')->name('tickets.')->group(function () {
            Route::get('/', [BusinessTicketController::class, 'index'])->name('index');
            Route::post('/', [BusinessTicketController::class, 'store'])->name('store');
            Route::get('{ticket}', [BusinessTicketController::class, 'show'])->name('show');
            Route::post('{ticket}/message', [BusinessTicketController::class, 'message'])->name('message');
        });

        Route::prefix('reserve')->name('reserve.')->group(function () {
            Route::get('reservables', [BusinessReserveController::class, 'reservables'])->name('reservables');
            Route::post('get_times', [BusinessReserveController::class, 'getTimes'])->name('getTimes');
            Route::post('reserve', [BusinessReserveController::class, 'reserve'])->name('reserve');
            Route::post('cancel', [BusinessReserveController::class, 'cancel'])->name('cancel');
            Route::post('reply', [BusinessReserveController::class, 'reply'])->name('reply');
        });

        Route::prefix('wallet')->name('wallet.')->group(function () {
            Route::get('/', [BusinessWalletController::class, 'index'])->name('index');
            Route::post('charge', [BusinessWalletController::class, 'charge'])->name('charge');
            Route::post('withdraw', [BusinessWalletController::class, 'withdraw'])->name('withdraw');
        });

    });

    Route::middleware(['auth:api', 'building_manager'])->name('building_manager.')->prefix('building_manager')->group(function () {
        Route::get('profile', [BuildingManagerProfileController::class, 'show'])->name('profile.show');
        Route::post('profile/image', [BuildingManagerProfileController::class, 'updateBuildingImage'])->name('profile.updateBuildingImage');
        Route::post('profile', [BuildingManagerProfileController::class, 'update'])->name('profile.update');
        Route::get('stats', [BuildingManagerProfileController::class, 'stats'])->name('profile.stats');
        Route::post('sendContractOtp', [BuildingManagerProfileController::class, 'sendContractOtp'])->name('profile.sendContractOtp');
       
       Route::get('units/late-fine-scope', [LateFineController::class, 'index']);
Route::post('units/late-fine-scope', [LateFineController::class, 'update']);
 Route::post('verifyContractOtp', [BuildingManagerProfileController::class, 'verifyContractOtp'])->name('profile.verifyContractOtp');
 Route::post('building_manager/proforma', [ProformaController::class, 'store'])
        ->name('v1.building_manager.proforma.store');
        Route::prefix('factors')->name('factors.')->group(function () {
            Route::get('/', [BuildingManagerFactorController::class, 'index'])->name('index');
             // پیش‌نمایش (بدون ذخیره) → JSON یا ?format=
            Route::post('preview', [BuildingManagerFactorController::class, 'preview'])->name('preview');
            // ذخیرهٔ نهایی فاکتور
            Route::post('/', [BuildingManagerFactorController::class, 'store'])->name('store');
            // نمایش فاکتور
            Route::get('{factor}', [BuildingManagerFactorController::class, 'show'])->name('show');
            // دانلود PDF
            Route::get('{factor}/pdf', [BuildingManagerFactorController::class, 'pdf'])->name('pdf');
            // نسخهٔ HTML
            Route::get('{factor}/html', [BuildingManagerFactorController::class, 'html'])->name('html');
            // ویرایش/حذف
            Route::put('{factor}', [BuildingManagerFactorController::class, 'update'])->name('update');
            Route::delete('{factor}', [BuildingManagerFactorController::class, 'destroy'])->name('destroy');
        });

        // پیش‌نمایش پروفرما (بدون ذخیره)
        Route::post('proforma/preview', [ProformaController::class, 'preview'])
            ->name('v1.building_manager.proforma.preview');

        // PDF پیش‌نمایش (خروجی HTML/PDF بر اساس پارامتر)
        Route::get('proforma/html', [ProformaController::class, 'showHtml'])
            ->name('v1.building_manager.proforma.html');
Route::get('features', [FeatureController::class, 'index']);
Route::post('features/{code}/purchase', [FeatureController::class, 'purchase']);

    // PDF رکورد ذخیره‌شده
    Route::get('building_manager/proforma/{proforma}/pdf', [ProformaController::class, 'showPdf'])
        ->name('v1.building_manager.proforma.pdf');

        Route::apiResource('announcements', BuildingManagerAnnouncementController::class)->only(['index', 'show', 'store', 'update', 'destroy']);

        Route::apiResource('units', BuildingManagerUnitController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
        Route::get('units/{unit}/invoices', [BuildingManagerInvoiceController::class, 'unitInvoices'])->name('units.invoices');
        Route::get('units/{unit}/transactions', [BuildingManagerTransactionController::class, 'unitTransactions'])->name('units.transactions');

        Route::get('invoices', [BuildingManagerInvoiceController::class, 'index'])->name('invoices.index');
        Route::get('invoices/{invoice}', [BuildingManagerInvoiceController::class, 'show'])->name('invoices.show');
        Route::post('invoices', [BuildingManagerInvoiceController::class, 'store'])->name('invoices.store');
        Route::put('invoices/{invoice}', [BuildingManagerInvoiceController::class, 'update'])->name('invoices.update');
        Route::delete('invoices/{invoice}', [BuildingManagerInvoiceController::class, 'destroy'])->name('invoices.destroy');
        Route::post('invoices/{invoice}/pay', [BuildingManagerInvoiceController::class, 'pay'])->name('invoices.pay');
        Route::get('invoices/{invoice}/pdf', [BuildingManagerInvoiceController::class, 'pdf'])->name('invoices.pdf');

        Route::post('debts/generate', [BuildingManagerDebtController::class, 'generate'])->name('debts.generate');
        Route::post('debts/assign', [BuildingManagerDebtController::class, 'assign'])->name('debts.assign');
        Route::get('debts', [BuildingManagerDebtController::class, 'index'])->name('debts.index');
        Route::get('debts/{debt}', [BuildingManagerDebtController::class, 'show'])->name('debts.show');
        Route::post('debts/{debt}/cancel', [BuildingManagerDebtController::class, 'cancel'])->name('debts.cancel');

        Route::get('transactions', [BuildingManagerTransactionController::class, 'index'])->name('transactions.index');
        Route::post('transactions/confirm', [BuildingManagerTransactionController::class, 'confirm'])->name('transactions.confirm');

        Route::prefix('reserve')->name('reserve.')->group(function () {
            Route::get('reservables', [BuildingManagerReserveController::class, 'reservables'])->name('reservables');
            Route::post('get_times', [BuildingManagerReserveController::class, 'getTimes'])->name('getTimes');
            Route::post('reserve', [BuildingManagerReserveController::class, 'reserve'])->name('reserve');
            Route::post('cancel', [BuildingManagerReserveController::class, 'cancel'])->name('cancel');
            Route::post('reply', [BuildingManagerReserveController::class, 'reply'])->name('reply');
        });

        Route::apiResource('modules', BuildingManagerModuleController::class)->only(['index']);
        Route::prefix('modules')->name('modules.')->group(function () {
            Route::post('buy', [BuildingManagerModuleController::class, 'buy'])->name('buy');
        });

        Route::prefix('polls')->name('polls.')->group(function () {
            Route::get('/', [BuildingManagerPollController::class, 'index'])->name('index');
            Route::post('/', [BuildingManagerPollController::class, 'store'])->name('store');
            Route::get('{poll}', [BuildingManagerPollController::class, 'show'])->name('show');
            Route::put('{poll}', [BuildingManagerPollController::class, 'update'])->name('update');
            Route::delete('{poll}', [BuildingManagerPollController::class, 'destroy'])->name('destroy');
            Route::post('{poll}/toggle', [BuildingManagerPollController::class, 'toggle'])->name('toggle');
            Route::post('{poll}/options', [BuildingManagerPollController::class, 'addOptions'])->name('options.add');
            Route::delete('{poll}/options/{option}', [BuildingManagerPollController::class, 'removeOption'])->name('options.remove');
        });

        Route::prefix('charity')->name('charity.')->group(function () {
            Route::get('/', [BuildingManagerCharityController::class, 'index'])->name('index');
            Route::post('pay', [BuildingManagerCharityController::class, 'pay'])->name('pay');
        });

        Route::prefix('pay_charge')->name('pay_charge.')->group(function () {
            Route::post('get_unit', [BuildingManagerPayChargeController::class, 'getUnit'])->name('getUnit');
            Route::post('get_invoices', [BuildingManagerPayChargeController::class, 'getInvoices'])->name('getInvoices');
            Route::post('pay', [BuildingManagerPayChargeController::class, 'pay'])->name('pay');
        });

        Route::prefix('rent')->name('rent.')->group(function () {
            Route::get('contracts', [BuildingManagerRentController::class, 'contracts'])->name('contracts');
            Route::get('contracts/{contract}', [BuildingManagerRentController::class, 'show'])->name('contracts.show');
            Route::get('contracts/{contract}/invoices', [BuildingManagerRentController::class, 'invoices'])->name('contracts.invoices');
            Route::post('contracts/{contract}/pay', [BuildingManagerRentController::class, 'pay'])->name('contracts.pay');
            Route::post('contracts', [BuildingManagerRentController::class, 'store'])->name('contracts.store');
            Route::put('contracts/{contract}', [BuildingManagerRentController::class, 'update'])->name('contracts.update');
            Route::delete('contracts/{contract}', [BuildingManagerRentController::class, 'destroy'])->name('contracts.destroy');
        });

        Route::prefix('debt-types')->name('debt_types.')->group(function () {
            Route::get('/', [BuildingManagerDebtTypeController::class, 'index'])->name('index');
            Route::post('/', [BuildingManagerDebtTypeController::class, 'store'])->name('store');
            Route::put('{debtType}', [BuildingManagerDebtTypeController::class, 'update'])->name('update');
            Route::delete('{debtType}', [BuildingManagerDebtTypeController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('wallet')->name('wallet.')->group(function () {
            Route::get('/', [BuildingManagerWalletController::class, 'index'])->name('index');
            Route::post('charge', [BuildingManagerWalletController::class, 'charge'])->name('charge');
            Route::post('withdraw', [BuildingManagerWalletController::class, 'withdraw'])->name('withdraw');
        });

    });

    Route::post('callback', [CallbackController::class, 'handle'])->name('callback');
});
