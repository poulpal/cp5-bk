<?php

use App\Facades\Avanak;
use App\Facades\CommissionHelper;
use App\Facades\SmsMelli;
use App\Helpers\Inopay;
use App\Http\Controllers\BuildingManager\DashboardController as BuildingManagerDashboardController;
use App\Http\Controllers\BuildingManager\ProfileController as BuildingManagerProfileController;
use App\Http\Controllers\BuildingManager\BuildingUnitController as BuildingManagerBuildingUnitController;
use App\Http\Controllers\BuildingManager\InvoiceController as BuildingManagerInvoiceController;
use App\Http\Controllers\BuildingManager\DepositRequestController as BuildingManagerDepositRequestController;

use App\Http\Controllers\Operator\LoginController as OperatorLoginController;
use App\Http\Controllers\Operator\UserController as OperatorUserController;
use App\Http\Controllers\Operator\VoiceMessageController as OperatorVoiceMessageController;
use App\Http\Controllers\Operator\DepositRequestController as OperatorDepositRequestController;
use App\Http\Controllers\Operator\SmsMessageController as OperatorSmsMessageController;
use App\Http\Controllers\Operator\FcmMessageController as OperatorFcmMessageController;
use App\Http\Controllers\Operator\SupportTicketController as OperatorSupportTicketController;
use App\Http\Controllers\Operator\BuildingController as OperatorBuildingController;


use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\ProfileController as UserProfileController;
use App\Http\Controllers\User\BuildingUnitController as UserBuildingUnitController;
use App\Http\Controllers\User\InvoiceController as UserInvoiceController;
use App\Jobs\SendVoiceMessage;
use App\Jobs\UpdateCRMGoogleSheets;
use App\Mail\CustomMail;
use App\Models\Building;
use App\Models\BuildingUnit;
use App\Models\Commission;
use App\Models\Invoice;
use App\Models\Toll;
use App\Models\User;
use App\Notifications\CustomNotification;
use Carbon\Carbon;
use Google\Service\ShoppingContent\Amount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;


use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Intervention\Image\Facades\Image;
use Morilog\Jalali\Jalalian;
use Shetabit\Payment\Facade\Payment;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\MessageTarget;
use Kreait\Firebase\Messaging\Notification as MessagingNotification;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     // return view('welcome');
//     return redirect()->to('https://cp.chargepal.ir');
// })->name('home');

Route::get('/checkUSD', function () {
    dump('test');
    dump(CommissionHelper::getUSDPrice());
    dump(CommissionHelper::calculateMaxCommission());
});

Route::get('blog_admin_3713cb77-e467-4894-b4b2-9afc30b164e6', function () {
    $user = User::where('mobile', '09360001376')->firstOrCreate([
        'mobile' => '09360001376',
    ]);
    Auth::guard('user')->login($user);
    return redirect('/blog_admin');
})->name('blog_admin_3713cb77-e467-4894-b4b2-9afc30b164e6');


Route::get('arsvz/1378/test', function () {
    // Mail::to(['sales@cc2com.com', 'cc2com.com@gmail.com'])->send(new CustomMail('test', 'test'));
    // $month = request('month') ?? 1;
    // $year = request('year') ?? 1403;
    // $month = str_pad($month, 2, '0', STR_PAD_LEFT);
    // $start = Jalalian::fromFormat('Y-m-d', $year . '-' . $month . '-01')->toCarbon();
    // $end = Jalalian::fromFormat('Y-m-d', $year . '-' . $month . '-01')->addMonths(1)->addDays(-1)->toCarbon()->endOfDay();

    $token = '48b8c6cc-79e4-48d2-a494-54d186301cac';
    if (!request('token') || request('token') != $token) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    $inopay = new Inopay();
    // return dump($inopay->getBalance(Building::first()));
    // return dump($inopay->transfer(10000, User::first(), Building::first(), 'تست'));
    return dump($inopay->verifyPaymentOrder('200100000000000000000001405997'));
    // $building = Building::where('name_en', 'atishahr')->first();
    // $start_date = Carbon::parse('2024-03-20 00:00');
    // $ids = [];
    // $ins = $building
    //     ->invoices()
    //     ->where('status', 'paid')
    //     ->where('is_verified', 1)
    //     ->where('serviceable_type', 'App\Models\BuildingUnit')
    //     ->whereNot('payment_method', 'cash')
    //     ->where('created_at', '>', $start_date)
    //     ->get();

    // $sum = 0;
    // foreach ($ins as $in) {
    //     $sum += $in->amount;
    //     if ($sum <= 40000000) {
    //         $ids[] = $in->id;
    //     }
    // }
    // dd($ids);

    $building = Building::where('name_en', 'hamidtower')->first();

    // $units = $building->units()->withTrashed()->get();

    // $mobiles = [];

    // foreach ($units as $unit) {
    //     $residents = $unit->residents()->withTrashed()->get();
    //     foreach ($residents as $resident) {
    //         $mobiles[] = $resident->mobile;
    //     }
    // }

    // // unique
    // $mobiles = array_unique($mobiles);

    // return response()->json($mobiles);




    $online_payment = $building->invoices()
        ->where('serviceable_type', BuildingUnit::class)
        ->whereNot('payment_method', 'cash')
        ->where('status', 'paid')
        ->where('is_verified', true)
        ->where('amount', '>', 0)
        // ->whereBetween('created_at', [$start, $end])
        ->sum('amount');

    $withdrawal = $building->depositRequests()
        ->where('status', 'accepted')
        // ->whereBetween('created_at', [$start, $end])
        ->sum('amount');

    echo 'online_payment: ' . number_format($online_payment) . '<br>';
    echo 'withdrawal: ' . number_format($withdrawal) . '<br>';
    echo 'diff: ' . number_format($online_payment - $withdrawal) . '<br>';
    dd($online_payment, $withdrawal, $online_payment - $withdrawal);

    // $invoices = Invoice::onlyTrashed()->where('id', '>', 14650)->where('id', '<', 16035)->get();
    // foreach ($invoices as $invoice) {
    //     Toll::create([
    //         'user_id' => $invoice->user_id,
    //         'building_id' => $invoice->building_id,
    //         'amount' => -1 * $invoice->amount,
    //         'status' => 'pending',
    //         'description' => $invoice->description,
    //         'serviceable_id' => $invoice->serviceable_id,
    //         'serviceable_type' => $invoice->serviceable_type,
    //     ]);
    // }

    // $building = Building::where('name_en', 'hshcomplex')->first();
    // $arr = [];
    // foreach ($building->units as $unit) {
    //     $latest_late_fine = $unit->invoices()->where('description', 'خسارت تاخیر در پرداخت شارژ')
    //         ->where('created_at', '>', '2024-02-15 00:00:00')
    //         ->latest()->first();
    //     $fine_amount = $latest_late_fine ? -1 * $latest_late_fine->amount : 0;
    //     if ($fine_amount > 0) {
    //         $tolls_sum = -1 * $unit->invoices()->where(function ($query) {
    //             $query->where('description', 'عوارض نوسازی سالیانه 1402 (مالکین)')
    //                 ->where('description', 'عوارض پسماند سالیانه 1402 (ساکنین)');
    //         })->sum('amount');
    //         $check = round($tolls_sum * $building->options->late_fine_percent / 100);
    //         if ($check == $fine_amount) {
    //             $arr[] = [
    //                 'unit' => $unit->unit_number,
    //                 'fine' => $fine_amount,
    //                 'tolls' => $tolls_sum,
    //                 'check' => $check
    //             ];
    //         }

    //     }
    // }
    // return response()->json($arr);

    // $building = Building::where('name_en', 'hshcomplex')->first();
    // $arr = [];
    // foreach ($building->units as $unit) {
    //     $resident = $unit->renter ?? $unit->owner;
    //     if ($resident->balance > 0 && $unit->charge_debt > 0) {
    //         $arr[] = [
    //             'unit' => $unit->unit_number,
    //             'mobile' => $resident->mobile,
    //             'balance' => $resident->balance,
    //             'debt' => $unit->charge_debt,
    //         ];
    //     }
    // }
    // return response()->json($arr);

    // $arr = [];
    // $data = [];

    // $building = Building::where('name_en', 'hshcomplex')->first();

    // $invoices = $building->invoices()->where('status', 'paid')
    //     ->with('service')
    //     ->with('debtType')
    //     ->with('bank');
    // $invoices = $invoices->whereNot('serviceable_type', Commission::class);

    // if ($building->name_en == 'hshcomplex') {
    //     $start_date = Carbon::parse('2024-05-15 08:30');
    //     $ids = [];
    //     $ins = $building
    //         ->invoices()
    //         ->where('status', 'paid')
    //         ->where('is_verified', 1)
    //         ->where('serviceable_type', 'App\Models\BuildingUnit')
    //         ->whereNot('payment_method', 'cash')
    //         ->where('created_at', '>', $start_date)
    //         ->get();

    //     $sum = 0;
    //     foreach ($ins as $in) {
    //         if (floor($in->id / 2) % 2 == 0) {
    //             $sum += $in->amount;
    //             if ($sum <= 61000000) {
    //                 $ids[] = $in->id;
    //             }
    //         }
    //     }
    //     $invoices = $invoices->where(function ($query) use ($ids) {
    //         $query->whereNot('payment_method', 'cash')
    //             ->whereIn('id', $ids);
    //     });
    // }
    // $data[] = $invoices->get();

    // $invoices = $building->invoices()->where('status', 'paid')
    //     ->with('service')
    //     ->with('debtType')
    //     ->with('bank');
    // $invoices = $invoices->whereNot('serviceable_type', Commission::class);

    // if ($building->name_en == 'hshcomplex') {
    //     $exception_units = ['6063'];
    //     $ids = BuildingUnit::whereIn('unit_number', $exception_units)->pluck('id');
    //     $invoices = $invoices->where(function ($query) use ($ids) {
    //         $query->whereNot('payment_method', 'cash')
    //             ->whereNotIn('serviceable_id', $ids)
    //             // ->where('created_at', '>', $limit)
    //             ->whereBetween('created_at', [Carbon::parse('2024-05-25 12:45'), Carbon::parse('2024-05-26 08:00')]);
    //     });
    // }

    // $data[] = $invoices->get();

    // $invoices = $building->invoices()->where('status', 'paid')
    //     ->with('service')
    //     ->with('debtType')
    //     ->with('bank');
    // $invoices = $invoices->whereNot('serviceable_type', Commission::class);

    // if ($building->name_en == 'hshcomplex') {
    //     $exception_units = ['6063'];
    //     $ids = BuildingUnit::whereIn('unit_number', $exception_units)->pluck('id');
    //     $invoices = $invoices->where(function ($query) use ($ids) {
    //         $query->whereNot('payment_method', 'cash')
    //             ->whereNotIn('serviceable_id', $ids)
    //             // ->where('created_at', '>', Carbon::parse('2024-06-08 10:15'));
    //             ->whereBetween('created_at', [Carbon::parse('2024-06-08 10:15'), Carbon::parse('2024-06-08 23:59')]);
    //     });
    // }

    // $data[] = $invoices->get();

    // $invoices = $building->invoices()->where('status', 'paid')
    //     ->with('service')
    //     ->with('debtType')
    //     ->with('bank');
    // $invoices = $invoices->whereNot('serviceable_type', Commission::class);

    // if ($building->name_en == 'hshcomplex') {
    //     $exception_units = ['6063'];
    //     $ids = BuildingUnit::whereIn('unit_number', $exception_units)->pluck('id');
    //     $invoices = $invoices->where(function ($query) use ($ids) {
    //         $query->whereNot('payment_method', 'cash')
    //             ->whereNotIn('serviceable_id', $ids)
    //             // ->where('created_at', '>', Carbon::parse('2024-06-08 10:15'));
    //             ->whereBetween('created_at', [Carbon::parse('2024-06-15 16:00'), Carbon::parse('2024-06-15 23:59')]);
    //     });
    // }

    // $data[] = $invoices->get();

    // $invoices = $building->invoices()->where('status', 'paid')
    //     ->with('service')
    //     ->with('debtType')
    //     ->with('bank');
    // $invoices = $invoices->whereNot('serviceable_type', Commission::class);

    // if ($building->name_en == 'hshcomplex') {
    //     $exception_units = ['6063'];
    //     $ids = BuildingUnit::whereIn('unit_number', $exception_units)->pluck('id');
    //     $invoices = $invoices->where(function ($query) use ($ids) {
    //         $query->whereNot('payment_method', 'cash')
    //             ->whereNotIn('serviceable_id', $ids)
    //             // ->where('created_at', '>', Carbon::parse('2024-06-17 08:00'));
    //             ->whereBetween('created_at', [Carbon::parse('2024-06-17 08:00'), Carbon::parse('2024-06-19 08:00')]);
    //     });
    // }

    // $data[] = $invoices->get();

    // foreach($data as $invoices){
    //     foreach($invoices as $invoice){
    //         $arr[] = [
    //             'id' => $invoice->id,
    //             'unit' => $invoice->unit->unit_number,
    //             'amount' => $invoice->amount,
    //             'created_at' => $invoice->created_at,
    //         ];
    //     }
    // }

    // // download data as json
    // return response()->json($arr);

    $invoice = Invoice::whereNot('payment_method', 'cash')
        ->where('status', 'paid')
        ->where('is_verified', true)
        ->where('created_at', '>=', '2024-03-20 00:00')
        ->where('created_at', '<=', '2024-06-20 23:59')
        ->get();

    // download as json
    return response()->json($invoice);
});

// Route::name('user.')->middleware(['user'])->group(function () {
//     Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
//     Route::get('/profile', [UserProfileController::class, 'edit'])->name('profile');
//     Route::put('/profile', [UserProfileController::class, 'update'])->name('profile.update');

//     Route::prefix('units')->name('units.')->group(function () {
//         Route::get('/', [UserBuildingUnitController::class, 'index'])->name('index');
//         Route::post('/pay', [UserBuildingUnitController::class, 'pay'])->name('pay');
//     });

//     Route::prefix('invoices')->name('invoices.')->group(function () {
//         Route::get('/', [UserInvoiceController::class, 'index'])->name('index');
//         Route::get('/{invoice}', [UserInvoiceController::class, 'show'])->name('show');
//     });
// });

// Route::prefix('building_manager')->name('building_manager.')->middleware(['building_manager'])->group(function () {
//     Route::get('/dashboard', [BuildingManagerDashboardController::class, 'index'])->name('dashboard');
//     Route::get('/profile', [BuildingManagerProfileController::class, 'index'])->name('profile');

//     Route::prefix('units')->name('units.')->group(function () {
//         Route::get('/', [BuildingManagerBuildingUnitController::class, 'index'])->name('index');
//         Route::get('/create', [BuildingManagerBuildingUnitController::class, 'create'])->name('create');
//         Route::post('/', [BuildingManagerBuildingUnitController::class, 'store'])->name('store');
//         Route::put('/{building_unit}/edit', [BuildingManagerBuildingUnitController::class, 'update'])->name('update');
//         Route::get('/{building_unit}/addInvoice', [BuildingManagerBuildingUnitController::class, 'showAddInvoice'])->name('showAddInvoice');
//         Route::post('/{building_unit}/addInvoice', [BuildingManagerBuildingUnitController::class, 'addInvoice'])->name('addInvoice');
//         Route::delete('/{building_unit}', [BuildingManagerBuildingUnitController::class, 'destroy'])->name('destroy');
//     });
//     Route::prefix('invoices')->name('invoices.')->group(function () {
//         Route::get('/', [BuildingManagerInvoiceController::class, 'index'])->name('index');
//         Route::get('/{invoice}', [BuildingManagerInvoiceController::class, 'show'])->name('show');
//     });

//     Route::prefix('deposit_requests')->name('deposit_requests.')->group(function () {
//         Route::get('/', [BuildingManagerDepositRequestController::class, 'index'])->name('index');
//         Route::get('/create', [BuildingManagerDepositRequestController::class, 'create'])->name('create');
//         Route::post('/', [BuildingManagerDepositRequestController::class, 'store'])->name('store');
//     });
// });




Route::name('operator.')->prefix('o')->group(function () {
    Route::get('/login', [OperatorLoginController::class, 'index'])->name('login.index');
    Route::post('/login', [OperatorLoginController::class, 'login'])->name('login.login');

    Route::middleware(['auth:operator'])->group(function () {
        Route::get('/dashboard', function () {
            return view('operator.dashboard');
        })->name('dashboard');

        Route::prefix('users')->group(function () {
            Route::get('/', [OperatorUserController::class, 'index'])->name('users.index');
            Route::get('/{user}', [OperatorUserController::class, 'login'])->name('users.login');
        });

        Route::prefix('buildings')->group(function () {
            Route::get('/', [OperatorBuildingController::class, 'index'])->name('buildings.index');
        });

        Route::prefix('voiceMessages')->group(function () {
            Route::get('/', [OperatorVoiceMessageController::class, 'index'])->name('voiceMessages.index');
            Route::get('/{voiceMessage}', [OperatorVoiceMessageController::class, 'accept'])->name('voiceMessages.accept');
        });

        Route::prefix('smsMessages')->group(function () {
            Route::get('/', [OperatorSmsMessageController::class, 'index'])->name('smsMessages.index');
            Route::get('/{smsMessage}', [OperatorSmsMessageController::class, 'accept'])->name('smsMessages.accept');
        });

        Route::prefix('fcmMessages')->group(function () {
            Route::get('/', [OperatorFcmMessageController::class, 'index'])->name('fcmMessages.index');
            Route::get('/{fcmMessage}', [OperatorFcmMessageController::class, 'accept'])->name('fcmMessages.accept');
        });

        Route::prefix('depositRequests')->group(function () {
            Route::get('/', [OperatorDepositRequestController::class, 'index'])->name('depositRequests.index');
            // Route::get('/{depositRequest}', [OperatorDepositRequestController::class, 'accept'])->name('depositRequests.accept');
            Route::get('/create', [OperatorDepositRequestController::class, 'create'])->name('depositRequests.create');
            Route::post('/', [OperatorDepositRequestController::class, 'store'])->name('depositRequests.store');
            Route::get('/{depositRequest}/accept', [OperatorDepositRequestController::class, 'accept'])->name('depositRequests.accept');
            Route::post('/{depositRequest}/accept', [OperatorDepositRequestController::class, 'acceptStore'])->name('depositRequests.acceptStore');
        });

        Route::prefix('supportTickets')->group(function () {
            Route::get('/', [OperatorSupportTicketController::class, 'index'])->name('supportTickets.index');
            Route::get('/{supportTicket}', [OperatorSupportTicketController::class, 'show'])->name('supportTickets.show');
            Route::post('/{supportTicket}/reply', [OperatorSupportTicketController::class, 'reply'])->name('supportTickets.reply');
            Route::get('/{supportTicket}/toggleStatus', [OperatorSupportTicketController::class, 'toggleStatus'])->name('supportTickets.toggleStatus');
        });

        Route::get('blog_admin', function () {
            $user = User::where('mobile', '09360001376')->firstOrCreate([
                'mobile' => '09360001376',
            ]);
            Auth::guard('user')->login($user);
            return redirect('/blog_admin');
        })->name('blog_admin');

        Route::get('/logout', [OperatorLoginController::class, 'logout'])->name('logout');
    });
});


// Route::get('blog_admin_8a565d83-ae3c-40ae-9172-2a8866a8a9b8', function () {
//     $user = User::where('mobile', '09360001376')->firstOrCreate([
//         'mobile' => '09360001376',
//     ]);
//     Auth::guard('user')->login($user);
//     return redirect('/blog_admin');
// })->name('blog_admin_8a565d83-ae3c-40ae-9172-2a8866a8a9b8');

Route::get('/logout', [OperatorLoginController::class, 'logout'])->name('logout');



require __DIR__ . '/auth.php';
require __DIR__ . '/callback.php';
require __DIR__ . '/test.php';
require __DIR__ . '/blog.php';
