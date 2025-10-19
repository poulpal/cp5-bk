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
use App\Http\Controllers\Api\V1\User\PollController as UserPollController;
use App\Http\Controllers\Api\V1\User\WalletController as UserWalletController;
use App\Http\Controllers\Api\V1\User\TollController as UserTollController;
use App\Http\Controllers\Api\V1\User\CostController as UserCostController;
use App\Http\Controllers\Api\V1\User\StockController as UserStockController;
use App\Http\Controllers\Api\V1\User\ForumPostController as UserForumPostController;
use App\Http\Controllers\Api\V1\User\CashController as UserCashController;
use App\Http\Controllers\Api\V1\User\ReservableController as UserReservableController;

use App\Http\Controllers\Api\V1\BuildingManager\ProfileController as BuildingManagerProfileController;
use App\Http\Controllers\Api\V1\BuildingManager\BuildingUnitController as BuildingManagerBuildingUnitController;
use App\Http\Controllers\Api\V1\BuildingManager\DepositRequestController as BuildingManagerDepositRequestController;
use App\Http\Controllers\Api\V1\BuildingManager\InvoiceController as BuildingManagerInvoiceController;
use App\Http\Controllers\Api\V1\BuildingManager\ContactController as BuildingManagerContactController;
use App\Http\Controllers\Api\V1\BuildingManager\AnnouncementController as BuildingManagerAnnouncementController;
use App\Http\Controllers\Api\V1\BuildingManager\VoiceMessageController as BuildingManagerVoiceMessageController;
use App\Http\Controllers\Api\V1\BuildingManager\PollController as BuildingManagerPollController;
use App\Http\Controllers\Api\V1\BuildingManager\ReserveController as BuildingManagerReserveController;
use App\Http\Controllers\Api\V1\BuildingManager\BuildingManagerController as BuildingManagerBuildingManagerController;
use App\Http\Controllers\Api\V1\BuildingManager\BuildingOptionsController as BuildingManagerBuildingOptionsController;
use App\Http\Controllers\Api\V1\BuildingManager\StockController as BuildingManagerStockController;
use App\Http\Controllers\Api\V1\BuildingManager\TollController as BuildingManagerTollController;
use App\Http\Controllers\Api\V1\BuildingManager\PlanController as BuildingManagerPlanController;
use App\Http\Controllers\Api\V1\BuildingManager\SmsMessageController as BuildingManagerSmsMessageController;
use App\Http\Controllers\Api\V1\BuildingManager\FcmMessageController as BuildingManagerFcmMessageController;
use App\Http\Controllers\Api\V1\BuildingManager\SupportTicketController as BuildingManagerSupportTicketController;
use App\Http\Controllers\Api\V1\BuildingManager\ModuleController as BuildingManagerModuleController;
use App\Http\Controllers\Api\V1\BuildingManager\DebtTypeController as BuildingManagerDebtTypeController;
use App\Http\Controllers\Api\V1\BuildingManager\FactorController as BuildingManagerFactorController;
use App\Http\Controllers\Api\V1\BuildingManager\BalanceController as BuildingManagerBalanceController;
use App\Http\Controllers\Api\V1\BuildingManager\ForumPostController as BuildingManagerForumPostController;
use App\Http\Controllers\Api\V1\BuildingManager\DownloadController as BuildingManagerDownloadController;
use App\Http\Controllers\Api\V1\BuildingManager\CashController as BuildingManagerCashController;


use App\Http\Controllers\Api\V1\Accounting\AccountController as AccountingAccountController;
use App\Http\Controllers\Api\V1\Accounting\DetailController as AccountingDetailController;
use App\Http\Controllers\Api\V1\Accounting\DocumnetController as AccountingDocumentController;
use App\Http\Controllers\Api\V1\Accounting\ReportController as AccountingReportController;
use App\Http\Controllers\Api\V1\BuildingManager\FeatureController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::name('v1.')->group(function () {
    Route::get('ping', function () {
        return response()->json([
            'success' => true,
            'message' => 'pong',
            'time' => now()->toDateTimeString(),
            'locale' => app()->getLocale(),
        ]);
    })->name('ping');

    Route::name('callback.')->prefix('callback')->group(function () {
        Route::post('sepehr', [CallbackController::class, 'sepehr'])->name('sepehr');
        Route::post('test', [CallbackController::class, 'test'])->name('local');
        Route::post('sep', [CallbackController::class, 'sep'])->name('sep');
        Route::get('pasargad', [CallbackController::class, 'pasargad'])->name('pasargad');
        Route::any('inopay', [CallbackController::class, 'inopay'])->name('inopay');
    });

    Route::name('public.')->prefix('public')->group(function () {
        Route::name('charge.')->prefix('charge')->group(function () {
            Route::get('getCharge', [PayChargeController::class, 'getCharge'])->name('getCharge');
            Route::post('payCharge', [PayChargeController::class, 'payCharge'])->name('payCharge');
        });

        Route::name('toll.')->prefix('toll')->group(function () {
            Route::get('/getToll', [PayChargeController::class, 'getToll'])->name('getToll');
            Route::post('/payToll', [PayChargeController::class, 'payToll'])->name('payToll');
        });

        Route::name('reserve.')->prefix('reserve')->group(function () {
            Route::get('/{building:name_en}', [ReserveController::class, 'index'])->name('index');
            Route::post('/{reservable}', [ReserveController::class, 'reserve'])->name('reserve');
        });

        Route::get('/building_manager/contract', [BuildingManagerProfileController::class, 'contract'])->name('profile.contract');
        Route::get('/building_manager/factors/{token}', [BuildingManagerFactorController::class, 'view'])->name('factors.view');

        Route::apiResource('plans', PlanController::class)->only(['index']);

        Route::apiResource('announcements', AnnouncementController::class)->only(['index']);

        Route::name('survey.')->prefix('survey')->group(function () {
            Route::get('/{survey:slug}', [SurveyController::class, 'show'])->name('show');
            Route::post('/{survey:slug}', [SurveyController::class, 'storeAnswer'])->name('storeAnswer');
        });

        Route::prefix('charity')->name('charity.')->group(function () {
            Route::post('pay', [CharityController::class, 'pay'])->name('pay');
        });
        Route::apiResource('modules', ModuleController::class)->only(['index']);

        Route::get('version', [VersionController::class, 'index'])->name('version');
        Route::get('banners', [BannerController::class, 'index'])->name('banners.index');
    });

    Route::name('auth.')->group(function () {
        Route::middleware(['guest'])->group(function () {
            Route::post('sendOtp', [LoginController::class, 'sendOtp'])->name('sendOtp');
            Route::post('login', [LoginController::class, 'login'])->name('login');
            Route::post('business/register', [BusinessRegisterController::class, 'register'])->name('business.register')->middleware(['auth:api']);
        });
        Route::middleware(['auth:api'])->group(function () {
            Route::get('getMe', [LoginController::class, 'getMe'])->name('getMe')->middleware(['auth:api']);
            Route::post('registerFCMToken', [LoginController::class, 'registerFCMToken'])->name('registerFCMToken')->middleware(['auth:api']);
            Route::post('changePassword', [LoginController::class, 'changePassword'])->name('changePassword')->middleware(['auth:api']);
            Route::get('logout', [LoginController::class, 'logout'])->name('logout')->middleware(['auth:api']);
        });
    });

    Route::middleware(['auth:api'])->name('user.')->prefix('user')->group(function () {
        Route::get('profile', [UserProfileController::class, 'show'])->name('profile.show');
        Route::put('profile', [UserProfileController::class, 'update'])->name('profile.update');

        Route::apiResource('units', UserBuildingUnitController::class)->only(['index', 'show']);
        Route::post('units/{unit}/pay', [UserBuildingUnitController::class, 'pay'])->name('units.pay');
        Route::post('units/{unit}/payWithWallet', [UserBuildingUnitController::class, 'payWithWallet'])->name('units.payWithWallet');
        Route::post('units/{unit}/addInvoice', [UserBuildingUnitController::class, 'addInvoice'])->name('units.addInvoice');
        Route::post('units/{unit}/payToll/{toll}', [UserBuildingUnitController::class, 'payToll'])->name('units.payToll');

        Route::apiResource('invoices', UserInvoiceController::class)->except(['update', 'destroy']);
        Route::apiResource('tolls', UserTollController::class)->only(['index']);

        Route::apiResource('transactions', UserTransactionController::class)->except(['update', 'destroy']);

        Route::apiResource('contacts', UserContactController::class)->only(['index']);

        Route::apiResource('announcements', UserAnnouncementController::class)->only(['index']);

        Route::apiResource('polls', UserPollController::class)->only(['index']);
        Route::post('polls/{poll}', [UserPollController::class, 'vote'])->name('polls.vote');

        Route::prefix('wallet')->name('wallet.')->group(function () {
            Route::get('transactions', [UserWalletController::class, 'transactions'])->name('transactions');
            Route::get('balance', [UserWalletController::class, 'balance'])->name('balance');
            Route::post('addBalance', [UserWalletController::class, 'addBalance'])->name('addBalance');
        });

        Route::get('costs', [UserCostController::class, 'index'])->name('costs.index');
        Route::get('stocks', [UserStockController::class, 'index'])->name('stocks.index');
        Route::get('cash', [UserCashController::class, 'index'])->name('cash.index');

        Route::apiResource('forumPosts', UserForumPostController::class)->only(['index', 'store']);
        Route::post('forumPosts/{forumPost}/like', [UserForumPostController::class, 'toggleLike'])->name('forumPosts.toggleLike');

        Route::get('reservables', [UserReservableController::class, 'index'])->name('reservables.index');
        Route::get('reservables/reservations', [UserReservableController::class, 'unitReserveHistory'])->name('reservables.unitReserveHistory');
        Route::post('reservables/reservations/{reservation}/cancel', [UserReservableController::class, 'cancel'])->name('reservables.cancel');
        Route::post('reservables/{reservable}', [UserReservableController::class, 'reserve'])->name('reservables.reserve');

    });

    Route::middleware(['auth:api', 'building_manager'])->name('building_manager.')->prefix('building_manager')->group(function () {
        Route::get('profile', [BuildingManagerProfileController::class, 'show'])->name('profile.show');
        Route::post('profile/image', [BuildingManagerProfileController::class, 'updateBuildingImage'])->name('profile.updateBuildingImage');
        Route::post('profile', [BuildingManagerProfileController::class, 'update'])->name('profile.update');
        Route::get('stats', [BuildingManagerProfileController::class, 'stats'])->name('profile.stats');
        Route::post('sendContractOtp', [BuildingManagerProfileController::class, 'sendContractOtp'])->name('profile.sendContractOtp');
        Route::post('verifyContractOtp', [BuildingManagerProfileController::class, 'verifyContractOtp'])->name('profile.verifyContractOtp');
 Route::post('building_manager/proforma', [ProformaController::class, 'store'])
        ->name('v1.building_manager.proforma.store');
        Route::prefix('factors')->name('factors.')->group(function () {
            Route::get('/', [BuildingManagerFactorController::class, 'index'])->name('index');
             // پیش‌نمایش (بدون ذخیره) → JSON یا ?format=html
    Route::post('building_manager/proforma/preview', [ProformaController::class, 'preview'])
        ->name('v1.building_manager.proforma.preview');

    // HTML رکورد ذخیره‌شده
    Route::get('building_manager/proforma/{id}/html', [ProformaController::class, 'showHtml'])
        ->name('v1.building_manager.proforma.html');
Route::get('features', [FeatureController::class, 'index']);
Route::post('features/{code}/purchase', [FeatureController::class, 'purchase']);

    // PDF رکورد ذخیره‌شده
    Route::get('building_manager/proforma/{id}/pdf', [ProformaController::class, 'showPdf'])
        ->name('v1.building_manager.proforma.pdf');
        });

        Route::apiResource('units', BuildingManagerBuildingUnitController::class);
        Route::middleware(['hasPlan'])->group(function () {
            Route::post('units/addMultipleDebt', [BuildingManagerBuildingUnitController::class, 'addMultipleDebt'])->name('units.addMultipleDebt');
            Route::post('units/addMultipleDeposit', [BuildingManagerBuildingUnitController::class, 'addMultipleDeposit'])->name('units.addMultipleDeposit');
            Route::post('units/addMultipleUnits', [BuildingManagerBuildingUnitController::class, 'addMultipleUnits'])->name('units.addMultipleUnits');
            Route::post('units/{unit}/residents', [BuildingManagerBuildingUnitController::class, 'addResident'])->name('units.residents.add');
            Route::delete('units/{unit}/residents/{resident}', [BuildingManagerBuildingUnitController::class, 'removeResident'])->name('units.residents.remove');
            Route::post('units/{unit}/addInvoice', [BuildingManagerBuildingUnitController::class, 'addInvoice'])->name('units.addInvoice');
            Route::post('units/multipleDelete', [BuildingManagerBuildingUnitController::class, 'multipleDestroy'])->name('units.multipleDestroy');
            Route::post('units/setMultipleChargeFee', [BuildingManagerBuildingUnitController::class, 'setMultipleChargeFee'])->name('units.setMultipleChargeFee');
            Route::get('downloadQrcodes', [BuildingManagerBuildingUnitController::class, 'qrcodes'])->name('units.qrcodes');
            Route::get('downloadAccountingReports', [BuildingManagerBuildingUnitController::class, 'accountingReports'])->name('units.accountingReports');

            Route::get('depositRequests/pdf', [BuildingManagerDepositRequestController::class, 'pdf'])->name('depositRequests.pdf');
            Route::apiResource('depositRequests', BuildingManagerDepositRequestController::class)->only(['index', 'store', 'show'])->middleware(['verifyBusiness']);

            Route::get('invoices/pdf', [BuildingManagerInvoiceController::class, 'pdf'])->name('invoices.pdf');
            Route::apiResource('invoices', BuildingManagerInvoiceController::class)->only(['index', 'store', 'show', 'destroy', 'update']);
            Route::post('invoices/addMultiple', [BuildingManagerInvoiceController::class, 'addMultiple'])->name('invoices.addMultiple');
            Route::put('invoices/{invoice}/verify', [BuildingManagerInvoiceController::class, 'verify'])->name('invoices.verify');
            Route::put('invoices/{invoice}/reject', [BuildingManagerInvoiceController::class, 'reject'])->name('invoices.reject');
            Route::post('invoices/multipleDelete', [BuildingManagerInvoiceController::class, 'multipleDestroy'])->name('invoices.multipleDestroy');
            Route::get('invoices/{invoice}/receipt', [BuildingManagerInvoiceController::class, 'receipt'])->name('invoices.receipt');


            Route::apiResource('contacts', BuildingManagerContactController::class);

            Route::apiResource('polls', BuildingManagerPollController::class);
            Route::prefix('polls')->name('polls.')->group(function () {
                Route::post('/{poll}/renew', [BuildingManagerPollController::class, 'renew'])->name('renew');
            });

            Route::prefix('reservables')->name('reservables.')->group(function () {
                Route::get('/', [BuildingManagerReserveController::class, 'index'])->name('index');
                Route::post('/', [BuildingManagerReserveController::class, 'store'])->name('store');
                Route::get('/{reservable}', [BuildingManagerReserveController::class, 'show'])->name('show');
                Route::put('/{reservable}', [BuildingManagerReserveController::class, 'update'])->name('update');
                Route::delete('/{reservable}', [BuildingManagerReserveController::class, 'destroy'])->name('destroy');
            });

            Route::apiResource('announcements', BuildingManagerAnnouncementController::class);

            Route::apiResource('voiceMessages', BuildingManagerVoiceMessageController::class)->only(['index', 'store', 'destroy']);

            Route::apiResource('buildingManagers', BuildingManagerBuildingManagerController::class)->only(['index', 'show', 'store', 'destroy']);

            // Route::apiResource('options', BuildingManagerBuildingOptionsController::class)->only(['index', 'update']);
            Route::prefix('options')->name('options.')->group(function () {
                Route::get('/', [BuildingManagerBuildingOptionsController::class, 'show'])->name('show');
                Route::post('/', [BuildingManagerBuildingOptionsController::class, 'update'])->name('update');
            });

            Route::apiResource('stocks', BuildingManagerStockController::class)->only(['index', 'show', 'store']);
            Route::prefix('stocks')->name('stocks.')->group(function () {
                Route::post('/{stock}', [BuildingManagerStockController::class, 'addTransaction'])->name('addTransaction');
            });


            Route::apiResource('tolls', BuildingManagerTollController::class)->only(['index', 'destroy']);
            Route::prefix('tolls')->name('tolls.')->group(function () {
                Route::post('/{toll}/cashPay', [BuildingManagerTollController::class, 'cashPay'])->name('cashPay');
                Route::post('{unit}/addInvoice', [BuildingManagerTollController::class, 'store'])->name('store');
                Route::post('addMultiple', [BuildingManagerTollController::class, 'addMultiple'])->name('addMultiple');
                Route::post('multipleDelete', [BuildingManagerTollController::class, 'multipleDestroy'])->name('multipleDestroy');
            });

            Route::prefix('plans')->name('plans.')->group(function () {
                Route::get('currentPlan', [BuildingManagerPlanController::class, 'currentPlan'])->name('currentPlan');
                Route::post('buyPlan', [BuildingManagerPlanController::class, 'buyPlan'])->name('buyPlan');
                Route::post('checkDiscountCode', [BuildingManagerPlanController::class, 'checkDiscountCode'])->name('checkDiscountCode');
            });

            Route::prefix('accounting')->name('accounting.')->group(function () {
                Route::prefix('accounts')->name('accounts.')->group(function () {
                    Route::get('/', [AccountingAccountController::class, 'index'])->name('index');
                    Route::put('/{account}', [AccountingAccountController::class, 'update'])->name('update');
                    Route::post('/{parent_account}', [AccountingAccountController::class, 'store'])->name('store');
                    Route::delete('/{account}', [AccountingAccountController::class, 'destroy'])->name('destroy');
                });
                Route::prefix('details')->name('details.')->group(function () {
                    Route::get('/', [AccountingDetailController::class, 'index'])->name('index');
                    Route::put('/{detail}', [AccountingDetailController::class, 'update'])->name('update');
                    Route::post('/', [AccountingDetailController::class, 'store'])->name('store');
                    Route::delete('/{detail}', [AccountingDetailController::class, 'destroy'])->name('destroy');
                });
                Route::prefix('documents')->name('documents.')->group(function () {
                    Route::get('/getNewDocumentNumber', [AccountingDocumentController::class, 'getNewDocumentNumber'])->name('getNewDocumentNumber');
                    Route::get('/', [AccountingDocumentController::class, 'index'])->name('index');
                    Route::get('/{document_number}', [AccountingDocumentController::class, 'show'])->name('show');
                    Route::post('/', [AccountingDocumentController::class, 'store'])->name('store');
                    Route::delete('/{document}', [AccountingDocumentController::class, 'destroy'])->name('destroy');
                });
                Route::prefix('reports')->name('reports.')->group(function () {
                    Route::get('journal', [AccountingReportController::class, 'journal'])->name('journal');
                    Route::post('ledger', [AccountingReportController::class, 'ledger'])->name('ledger');
                    Route::get('trialBalance', [AccountingReportController::class, 'trialBalance'])->name('trialBalance');
                    Route::get('profitAndLoss', [AccountingReportController::class, 'profitAndLoss'])->name('profitAndLoss');
                    Route::get('balanceSheet', [AccountingReportController::class, 'balanceSheet'])->name('balanceSheet');
                });
            });

            Route::apiResource('smsMessages', BuildingManagerSmsMessageController::class)->only(['index', 'store', 'destroy']);
            Route::prefix('smsMessages')->name('smsMessages.')->group(function () {
                Route::get('getSmsPrice', [BuildingManagerSmsMessageController::class, 'getSmsPrice'])->name('getSmsPrice');
                Route::get('getBalance', [BuildingManagerSmsMessageController::class, 'getBalance'])->name('getBalance');
                Route::post('addBalance', [BuildingManagerSmsMessageController::class, 'addBalance'])->name('addBalance');
            });

            Route::apiResource('fcmMessages', BuildingManagerFcmMessageController::class)->only(['index', 'store', 'destroy']);

            Route::apiResource('supportTickets', BuildingManagerSupportTicketController::class)->only(['index', 'store', 'show']);
            Route::prefix('supportTickets')->name('supportTickets.')->group(function () {
                Route::post('/{supportTicket}/reply', [BuildingManagerSupportTicketController::class, 'reply'])->name('reply');
            });

            Route::apiResource('modules', BuildingManagerModuleController::class)->only(['index']);
            Route::prefix('modules')->name('modules.')->group(function () {
                Route::post('buy', [BuildingManagerModuleController::class, 'buy'])->name('buy');
                Route::post('checkDiscountCode', [BuildingManagerModuleController::class, 'checkDiscountCode'])->name('checkDiscountCode');
            });

            Route::apiResource('debtTypes', BuildingManagerDebtTypeController::class)->only(['index']);
            Route::apiResource('balances', BuildingManagerBalanceController::class)->only(['index']);
            Route::apiResource('cash', BuildingManagerCashController::class)->only(['index']);
            Route::put('cash/{cash_id}', [BuildingManagerCashController::class, 'changeBalance'])->name('cash.changeBalance');

            Route::apiResource('forumPosts', BuildingManagerForumPostController::class)->only(['index', 'store']);
            Route::post('forumPosts/{forumPost}/like', [BuildingManagerForumPostController::class, 'toggleLike'])->name('forumPosts.toggleLike');

            Route::post('excelDownload', [BuildingManagerDownloadController::class, 'downloadExcel'])->name('downloadExcel');
        });
    });
});

