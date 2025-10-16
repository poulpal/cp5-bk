<?php

namespace App\Console\Commands;

use App\Helpers\Inopay;
use App\Jobs\UpdateCRMGoogleSheets;
use App\Mail\CustomMail;
use App\Mail\NewBuildingMail;
use App\Models\Accounting\AccountingAccount;
use App\Models\Accounting\AccountingDetail;
use App\Models\Accounting\AccountingDocument;
use App\Models\Accounting\AccountingTransaction;
use App\Models\Building;
use App\Models\BuildingManager;
use App\Models\BuildingUnit;
use App\Models\Commission;
use App\Models\DebtType;
use App\Models\DepositRequest;
use App\Models\Factor;
use App\Models\Invoice;
use App\Models\Module;
use App\Models\PendingDeposit;
use App\Models\Toll;
use App\Models\User;
use App\Notifications\BuildingManager\UserPaidCharge;
use App\Notifications\CustomFCMNotification;
use App\Notifications\OtpNotification;
use App\Notifications\User\ChargeAddedNotfication;
use App\Notifications\User\CustomNotification;
use App\Notifications\User\SmsNotification;
use App\Notifications\User\VoiceNotification;
use App\Notifications\User\WelcomeNotification;
use App\Observers\Accounting\BuildingUnitObserver;
use App\Observers\Accounting\DepositRequestObserver;
use App\Observers\Accounting\InvoiceObserver;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Morilog\Jalali\Jalalian;
use ZipArchive;

class HotFix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hotfix:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // $tolls = Toll::where('status', 'paid')->get();
        // foreach ($tolls as $toll) {
        //     $debt = new Invoice();
        //     $debt->building_id = $toll->building_id;
        //     $debt->amount = -1 * $toll->amount;
        //     $debt->status = 'paid';
        //     $debt->payment_method = 'cash';
        //     $debt->description = $toll->description;
        //     $debt->serviceable_id = $toll->serviceable_id;
        //     $debt->serviceable_type = $toll->serviceable_type;
        //     $debt->is_verified = true;
        //     $debt->created_at = $toll->created_at;
        //     $debt->save();

        //     $invoice = $toll->invoices()->where('status', 'paid')->first();
        //     if ($invoice) {
        //         $deposit = $invoice->replicate();
        //         $deposit->serviceable_id = $toll->serviceable_id;
        //         $deposit->serviceable_type = $toll->serviceable_type;
        //         $deposit->created_at = $toll->created_at;
        //         $deposit->save();

        //         $pending_deposit = new PendingDeposit();
        //         $pending_deposit->invoice()->associate($deposit);
        //         $pending_deposit->building()->associate($toll->unit->building);
        //         $pending_deposit->save();

        //         $toll->unit->building->increment('balance', $toll->amount);
        //     } else {
        //         $deposit = new Invoice();
        //         $deposit->building_id = $toll->building_id;
        //         $deposit->amount = $toll->amount;
        //         $deposit->status = 'paid';
        //         $deposit->payment_method = 'cash';
        //         $deposit->description = $toll->description;
        //         $deposit->serviceable_id = $toll->serviceable_id;
        //         $deposit->serviceable_type = $toll->serviceable_type;
        //         $deposit->is_verified = true;
        //         $deposit->created_at = $toll->created_at;
        //         $deposit->save();
        //     }
        // }

        // $building = Building::where('name_en', 'hshcomplex')->first();
        // $units = $building->units;
        //     foreach ($units as $unit) {
        //         $charge_fee = $unit->charge_fee;
        //         $charge_debt = $unit->charge_debt;
        //         if ($unit->charge_debt > 0) {
        //             $send_time = Carbon::now()->startOfDay()->addHours(20);
        //             $resident = $unit->renter ?? $unit->owner;
        //             $resident->notify(
        //                 (new ChargeAddedNotfication($charge_fee, $unit->charge_debt, $unit->token))
        //                     ->delay($send_time)
        //             );
        //     }
        // }

        // $buildings = Building::all();
        // foreach ($buildings as $building) {
        //     try {
        //         $building->is_verified = $building->mainBuildingManagers()->first()->details->is_verified;
        //         $building->save();
        //     } catch (\Throwable $th) {
        //         continue;
        //     }
        // }

        // $plans = \App\Models\Plan::all();
        // foreach ($plans as $plan) {
        //     $plan->delete();
        // }

        // $plans = [
        //     [
        //         'title' => 'آزمایشی',
        //         'slug' => 'free',
        //         'order' => -1,
        //         'features' => [
        //         ],
        //         'durations' => [
        //             [
        //                 'days' => 7,
        //                 'price' => 0,
        //             ]
        //         ],
        //     ],
        //     [
        //         'title' => 'نقره ای',
        //         'slug' => 'silver',
        //         'order' => 1,
        //         'features' => [
        //             'سیستم مدیریت ساختمان',
        //             'داشبورد مدیریت',
        //             'داشبورد ساکنین',
        //             'درگاه پرداخت پیامکی',
        //             'فاکتور الکترونیکی',
        //             'وضعیت واحدها',
        //             'صورتحساب',
        //             'بدهی ها',
        //             'هزینه ها',
        //             'اطلاعیه ها',
        //             'درگاه پرداخت QR',
        //             'درآمدها',
        //             'پیام متنی (اس ام اس)',
        //             'نظرسنجی',
        //             'گزارشات مالی',
        //         ],
        //         'durations' => [
        //             [
        //                 'months' => 3,
        //                 'price' => 570000,
        //             ],
        //             [
        //                 'months' => 6,
        //                 'price' => 990000,
        //             ],
        //             [
        //                 'months' => 12,
        //                 'price' => 1900000,
        //             ],
        //         ],
        //     ],
        //     [
        //         'title' => 'طلایی',
        //         'slug' => 'gold',
        //         'order' => 2,
        //         'features' => [
        //             'سیستم مدیریت ساختمان',
        //             'داشبورد مدیریت',
        //             'داشبورد ساکنین',
        //             'درگاه پرداخت پیامکی',
        //             'فاکتور الکترونیکی',
        //             'وضعیت واحدها',
        //             'صورتحساب',
        //             'بدهی ها',
        //             'هزینه ها',
        //             'اطلاعیه ها',
        //             'درگاه پرداخت QR',
        //             'درآمدها',
        //             'پیام متنی (اس ام اس)',
        //             'نظرسنجی',
        //             'گزارشات مالی',
        //             'عوارض',
        //             'انبارداری',
        //             'انتخابات',
        //             'رزرو مشاعات و امکانات',
        //             'درگاه پرداخت اختصاصی',
        //         ],
        //         'durations' => [
        //             [
        //                 'months' => 3,
        //                 'price' => 1770000,
        //             ],
        //             [
        //                 'months' => 6,
        //                 'price' => 2900000,
        //             ],
        //             [
        //                 'months' => 12,
        //                 'price' => 5900000,
        //             ],
        //         ],
        //     ],
        //     [
        //         'title' => 'پلاتینیوم',
        //         'slug' => 'platinum',
        //         'order' => 3,
        //         'features' => [
        //             'سیستم مدیریت ساختمان',
        //             'داشبورد مدیریت',
        //             'داشبورد ساکنین',
        //             'درگاه پرداخت پیامکی',
        //             'فاکتور الکترونیکی',
        //             'وضعیت واحدها',
        //             'صورتحساب',
        //             'بدهی ها',
        //             'هزینه ها',
        //             'اطلاعیه ها',
        //             'درگاه پرداخت QR',
        //             'درآمدها',
        //             'پیام متنی (اس ام اس)',
        //             'نظرسنجی',
        //             'گزارشات مالی',
        //             'عوارض',
        //             'انبارداری',
        //             'انتخابات',
        //             'رزرو مشاعات و امکانات',
        //             'درگاه پرداخت اختصاصی',
        //             'تسویه حساب فوری',
        //             'پیام های صوتی (تلفن گویا)',
        //             'حواله وجه ',
        //             'حسابداری پیشرفته'
        //         ],
        //         'durations' => [
        //             [
        //                 'months' => 3,
        //                 'price' => 2670000,
        //             ],
        //             [
        //                 'months' => 6,
        //                 'price' => 4800000,
        //             ],
        //             [
        //                 'months' => 12,
        //                 'price' => 8900000,
        //             ],
        //         ],
        //     ],
        //     [
        //         'title' => 'VIP',
        //         'slug' => 'vip',
        //         'order' => -2,
        //         'features' => [

        //         ],
        //         'durations' => [
        //             [
        //                 'months' => 3,
        //                 'price' => 'call',
        //             ],
        //             [
        //                 'months' => 6,
        //                 'price' => 'call',
        //             ],
        //             [
        //                 'months' => 12,
        //                 'price' => 'call',
        //             ],
        //         ],
        //     ],
        // ];

        // foreach ($plans as $plan) {
        //     \App\Models\Plan::create($plan);
        // }

        // $unit = \App\Models\BuildingUnit::where('unit_number', '5203')->where('building_id', 2)->first();

        // DB::transaction(function () use ($unit) {
        //     $resident = $unit->renter ?? $unit->owner;
        //     $deposit_amount = 1771479.7;
        //     if ($resident->balance < $deposit_amount || $unit->charge_debt <= 0) {
        //         return false;
        //     }
        //     $invoice = Invoice::create([
        //         'user_id' => $resident->id,
        //         'payment_method' => 'wallet',
        //         'amount' => $deposit_amount,
        //         'building_id' => $unit->building->id,
        //         'serviceable_id' => $unit->id,
        //         'serviceable_type' => BuildingUnit::class,
        //         'description' => 'پرداخت آنلاین شارژ',
        //         'status' => 'paid'
        //     ]);

        //     $unit->charge_debt = round($unit->charge_debt - $deposit_amount, 1);
        //     $unit->save();

        //     $invoice->user->balance = round($invoice->user->balance - $deposit_amount, 1);
        //     $invoice->user->save();

        //     $unit->building->balance = round($unit->building->balance + $deposit_amount, 1);
        //     $unit->building->save();

        //     if ($unit->building->options->send_building_manager_payment_notification) {
        //         foreach ($unit->building->mainBuildingManagers as $manager) {
        //             $manager->notify(new UserPaidCharge($invoice->amount, $invoice->user->full_name, $invoice->user->mobile, $invoice->id, $unit->unit_number));
        //         }
        //     }

        //     Mail::to(['arcenciel.ir@gmail.com', 'saman.moayeri@gmail.com'])->send(
        //         new CustomMail(
        //             'پرداخت شارژ از کیف پول - ساختمان : ' . $unit->building->name . " - " . $invoice->id ?? "",
        //             "نام ساختمان : " . $unit->building->name . "<br>" .
        //                 "واحد : " . $unit->unit_number . " - " . $invoice->user->mobile . "<br>" .
        //                 "مبلغ : " . number_format($invoice->amount * 10) . " ریال" . "<br>" .
        //                 "شماره ارجاع : " . ($invoice->id ?? "")
        //         )
        //     );

        //     $pending_deposit = new PendingDeposit();
        //     $pending_deposit->invoice()->associate($invoice);
        //     $pending_deposit->building()->associate($unit->building);
        //     $pending_deposit->save();
        // });


        // $surveys = \App\Models\Survey::all();

        // foreach ($surveys as $survey) {
        //     $survey->forceDelete();
        // }

        // $survey = \App\Models\Survey::create([
        //     'title' => 'مشاوره رایگان شارژپل',
        //     'slug' => 'chargepal',
        //     'welcome_message' => 'همراه عزیز شارژپل
        //     وقت شما بخیر
        //     ',
        //     'end_message' => 'ممنونم از انتخاب شما
        //     کارشناسان ما در اسرع وقت با شما تماس میگیرند',
        //     'description' => 'برای مشاوره رایگان، ما نیاز به کمی اطلاعات از شما داریم تا بهترین گزینه رو به شما پیشنهاد بدیم.
        //     اطلاعات شما کمک میکنه تا ما کاربردی ترین و به صرفه ترین پکیج رو بر اساس نیازها و مشکلات ساختمانتون به شما معرفی کنیم.
        //     ',
        //     'eta' => 4,
        //     'questions' => [
        //         [
        //             'title' => 'نوع ساختمان خود را مشخص نمایید',
        //             'type' => 'radio',
        //             'options' => [
        //                 "مسکونی",
        //                 "تجاری",
        //                 "اداری",
        //                 "شهرک",
        //             ],
        //             "has_other" => true,
        //         ],
        //         [
        //             'title' => 'تعداد واحد های خود را وارد نمایید',
        //             'type' => 'text',
        //             'validation' => 'number',
        //         ],
        //         [
        //             'title' => 'لطفا چالشهای خود در زمینه مدیریت آپارتمان را مشخص نمایید',
        //             'type' => 'checkbox',
        //             'options' => [
        //                 "دریافت شارژ از ساکنین",
        //                 "عدم همکاری در امور اجرایی",
        //                 "اطلاع رسانی به ساکنین",
        //                 "شفاف سازی مالی",
        //                 "کمبود وقت برای مدیریت",
        //                 "اختلاف در محاسبات مالی",
        //                 "نگهداری مستندات هزینه های ساختمان",
        //                 "بی نظمی",
        //                 "رعایت نکردن قوانین",
        //                 "پیدا کردن اشخاص برای انجام امور ساختمان",
        //                 "نداشتن تجربه مدیریت ساختمان",
        //             ],
        //         ],
        //         [
        //             'title' => 'از چه طریقی با شارژپل آشنا شده اید؟',
        //             'type' => 'radio',
        //             'options' => [
        //                 "سایت",
        //                 "شبکه های اجتماعی",
        //                 "معرفی آشنایان",
        //                 "موتورهای جستجو",
        //             ],
        //             "has_other" => true,
        //         ],
        //         [
        //             'title' => 'نام شهر خود را بنویسید',
        //             'type' => 'text',
        //         ],
        //         [
        //             'title' => 'نام محله خود را بنویسید',
        //             'type' => 'text',
        //         ],
        //         [
        //             'title' => 'برای مشاوره چه روشی را می پسندید؟',
        //             'type' => 'radio',
        //             'options' => [
        //                 "واتسپ (پیام متنی)",
        //                 "تلگرام (پیام متنی)",
        //                 "گفت گوی تلفنی",
        //                 "ویدئو کنفرانس",
        //                 "جلسه حضوری",
        //             ],
        //         ],
        //         [
        //             'title' => 'چه زمانی از روز برای شما مناسب است؟',
        //             'type' => 'radio',
        //             'options' => [
        //                 "9 الی 12",
        //                 "12 الی 15",
        //                 "15 الی 18",
        //             ],
        //         ],
        //         [
        //             'title' => 'لطفا شماره تماس خود را برای هماهنگی جلسه مشاوره وارد نمایید',
        //             'type' => 'text',
        //             'validation' => 'mobile',
        //         ],
        //         [
        //             'title' => 'نام و نام خانوادگی',
        //             'type' => 'text',
        //         ],
        //     ],
        // ]);

        // $buildings = Building::where('name_en', 'atishahr')->get();

        // foreach ($buildings as $building) {
        //     foreach ($building->units as $unit) {
        //         if ($unit->charge_debt > 0) {
        //             $resident = $unit->renter ?? $unit->owner;
        //             $charge_debt = round($unit->charge_debt);
        //             $text = "با درود. بدهی آپارتمان شما بابت شارژ؛$charge_debt  تومان می باشد. لطفا از طریق سایتِ شارْژْپَلْ دات  آی آر نسبت به پرداخت آن اقدام نمایید";
        //             $resident->notify(new VoiceNotification($unit, $text));
        //         }
        //     }
        // }

        // AccountingAccount::truncate();
        // AccountingDetail::truncate();
        // AccountingDocument::truncate();
        // AccountingTransaction::truncate();
        // DebtType::truncate();

        // $buildings = Building::all();

        // foreach ($buildings as $building) {
        //     try {
        //         // dispatch_sync(new \App\Jobs\Accounting\AddBuildingAccountingAccounts($building->id));
        //         $max_code = $building->accountingDetails()->max('code') ?? 100000;
        //         $code = $max_code + 1;

        //         $building->accountingDetails()->create([
        //             'name' => 'بانک IR' . str($building->mainBuildingManagers()->first()->details->sheba_number),
        //             'code' => $code,
        //             'type' => 'bank',
        //         ]);

        //         $max_code = $building->accountingDetails()->max('code') ?? 100000;
        //         $code = $max_code + 1;

        //         $building->accountingDetails()->create([
        //             'name' => 'صندوق شارژپل',
        //             'code' => $code,
        //             'type' => 'cash',
        //         ]);
        //         $unit_observer = new BuildingUnitObserver();
        //         $deposit_request_observer = new DepositRequestObserver();
        //         $invoice_observer = new InvoiceObserver();
        //         foreach ($building->units as $unit) {
        //             $unit_observer->created($unit);
        //         }
        //         // foreach ($building->depositRequests as $deposit_request) {
        //         //     $deposit_request_observer->created($deposit_request);
        //         // }
        //         // foreach ($building->invoices as $invoice) {
        //         //     $invoice_observer->created($invoice);
        //         // }

        //         $debtTypes = [
        //             'شارژ جاری',
        //             'شارژ عمرانی',
        //             'جریمه دیرکرد',
        //             'تخفیف',
        //         ];

        //         foreach ($debtTypes as $debtType) {
        //             $building->debtTypes()->create([
        //                 'name' => $debtType,
        //                 'receivable_accounting_account_id' => $building->accountingAccounts()->where('code', '1305')->first()->id,
        //                 'income_accounting_account_id' => $building->accountingAccounts()->where('code', '6103')->first()->id,
        //             ]);
        //         }

        //         foreach ($building->invoices as $invoice) {
        //             $invoice->update([
        //                 'debt_type_id' => $building->debtTypes()->where('name', 'شارژ جاری')->first()->id,
        //             ]);
        //         }


        //     } catch (\Throwable $th) {
        //         Log::error($th);
        //     }
        // }

        // dispatch_sync(new UpdateCRMGoogleSheets());

        // $json_file = file_get_contents(resource_path('articles.json'));
        // $articles = json_decode($json_file, true)['data'];
        // foreach ($articles as $article) {
        //     try {
        //         if (\BinshopsBlog\Models\BinshopsBlogPost::where('slug', $article['nid'])->exists()) {
        //             continue;
        //         }
        //         $post = new \BinshopsBlog\Models\BinshopsBlogPost();
        //         $post->slug = $article['nid'];
        //         $post->title = $article['subject'];
        //         $post->post_body = $article['content'];
        //         $post->posted_at = $article['shamsi'] == "0000-00-00" ? Carbon::now()->addYears(-2) : Jalalian::fromFormat('Y-m-d', $article['shamsi'])->toCarbon();
        //         $post->created_at = $article['shamsi'] == "0000-00-00" ? Carbon::now()->addYears(-2) : Jalalian::fromFormat('Y-m-d', $article['shamsi'])->toCarbon();
        //         $post->updated_at = $article['shamsi'] == "0000-00-00" ? Carbon::now()->addYears(-2) : Jalalian::fromFormat('Y-m-d', $article['shamsi'])->toCarbon();
        //         $post->save();
        //         $this->info('Post ' . $article['nid'] . ' created');
        //     } catch (\Throwable $th) {
        //         $this->error('Post ' . $article['nid'] . ' failed');
        //     }
        // }


        // $building = Building::where('name_en', 'hshcomplex')->first();
        // foreach ($building->units as $unit) {
        //     $unit->update([
        //         'charge_fee' => round($unit->charge_fee * 1.45, 1),
        //     ]);
        // }


        // $modules = [
        //     [
        //         'title' => 'مدیریت 16 واحد',
        //         'slug' => 'base-16',
        //         'order' => 1,
        //         'type' => 'base',
        //         'price' => 0,
        //         'features' => [
        //             'limit' => 16,
        //         ],
        //         'description' => 'پکیج پایه مدیریت ساختمان با قابلیت مدیریت حداکثر 16 واحد',
        //     ],
        //     [
        //         'title' => 'مدیریت 30 واحد',
        //         'slug' => 'base-30',
        //         'order' => 2,
        //         'type' => 'base',
        //         'price' => 1000000,
        //         'features' => [
        //             'limit' => 30,
        //         ],
        //         'description' => 'پکیج پایه مدیریت ساختمان با قابلیت مدیریت حداکثر 30 واحد',
        //     ],
        //     [
        //         'title' => 'مدیریت 100 واحد',
        //         'slug' => 'base-100',
        //         'order' => 3,
        //         'type' => 'base',
        //         'price' => 1500000,
        //         'features' => [
        //             'limit' => 100,
        //         ],
        //         'description' => 'پکیج پایه مدیریت ساختمان با قابلیت مدیریت حداکثر 100 واحد',
        //     ],
        //     [
        //         'title' => 'مدیریت 300 واحد',
        //         'slug' => 'base-300',
        //         'order' => 4,
        //         'type' => 'base',
        //         'price' => 2000000,
        //         'features' => [
        //             'limit' => 300,
        //         ],
        //         'description' => 'پکیج پایه مدیریت ساختمان با قابلیت مدیریت حداکثر 300 واحد',
        //     ],
        //     [
        //         'title' => 'مدیریت نامحدود واحد',
        //         'slug' => 'base-inf',
        //         'order' => 5,
        //         'type' => 'base',
        //         'price' => 2500000,
        //         'features' => [
        //             'limit' => 500000,
        //         ],
        //         'description' => 'پکیج پایه مدیریت ساختمان با قابلیت مدیریت نامحدود واحد',
        //     ],
        //     [
        //         'title' => 'حسابداری پایه',
        //         'slug' => 'accounting-basic',
        //         'order' => 6,
        //         'type' => 'accounting',
        //         'price' => 0,
        //         'description' => 'ثبت سند اتوماتیک ، مشاهده اسناد',
        //     ],
        //     [
        //         'title' => 'حسابداری پیشرفته 1',
        //         'slug' => 'accounting-advanced-1',
        //         'order' => 7,
        //         'type' => 'accounting',
        //         'price' => 1900000,
        //         'description' => 'ثبت سند دستی، تغییر کدینگ، گزارش پایه',
        //     ],
        //     [
        //         'title' => 'حسابداری پیشرفته 2',
        //         'slug' => 'accounting-advanced-2',
        //         'order' => 8,
        //         'type' => 'accounting',
        //         'price' => 2900000,
        //         'description' => 'گزارش پیشرفته ، ترازنامه، صورت سود و زیان',
        //     ],
        //     [
        //         'title' => 'انبارداری',
        //         'slug' => 'stocks',
        //         'order' => 9,
        //         'type' => 'extra',
        //         'price' => 900000,
        //         'description' => 'انبارداری',
        //     ],
        //     [
        //         'title' => 'نظرسنجی و رزرو',
        //         'slug' => 'reserve-and-poll',
        //         'order' => 10,
        //         'type' => 'extra',
        //         'price' => 900000,
        //         'description' => 'نظرسنجی ، انتخابات، رزرو مشاعات',
        //     ],
        //     [
        //         'title' => 'جریمه و تخفیف',
        //         'slug' => 'fine-and-reward',
        //         'order' => 11,
        //         'type' => 'extra',
        //         'price' => 900000,
        //         'description' => 'اعمال اتوماتیک جریمه دیرکرد و تخفیف خوشحسابی',
        //     ],
        // ];

        // Module::truncate();

        // foreach ($modules as $module) {
        //     Module::create($module);
        // }

        // foreach (Building::all() as $building) {
        //     if ($building->plan_slug == 'free'){
        //         $modules = Module::where('price', 0)->get();
        //         $arr = [];
        //         foreach ($modules as $module) {
        //             $arr[$module->slug] = [
        //                 'starts_at' => $building->created_at,
        //                 'ends_at' => null,
        //                 'price' => $module->price,
        //             ];
        //         }
        //         $building->modules()->sync($arr);
        //     }else{
        //         $modules = Module::whereIn('slug', ['base-inf', 'accounting-basic', 'accounting-advanced-1', 'accounting-advanced-2', 'stocks', 'reserve-and-poll', 'fine-and-reward'])->get();
        //         $arr = [];
        //         foreach ($modules as $module) {
        //             $arr[$module->slug] = [
        //                 'starts_at' => $building->created_at,
        //                 'ends_at' => $building->plan_expires_at,
        //                 'price' => $module->price,
        //             ];
        //         }
        //         $building->modules()->sync($arr);
        //     }
        // }

        // $building = Building::where('name_en', 'atishahr')->first();
        // $accounts_array = json_decode(file_get_contents(resource_path('atishahr.json')), true);

        // foreach ($accounts_array as $account) {
        //     $created_account = $building->accountingAccounts()->create([
        //         'name' => $account['name'],
        //         'code' => $account['code'],
        //         'type' => $account['type'],
        //         // 'is_locked' => true,
        //     ]);
        //     if (isset($account['children'])) {
        //         foreach ($account['children'] as $child) {
        //             $created_child = $building->accountingAccounts()->create([
        //                 'name' => $child['name'],
        //                 'code' => $child['code'],
        //                 'type' => $child['type'],
        //                 // 'is_locked' => true,
        //                 'parent_id' => $created_account->id,
        //             ]);
        //             if (isset($child['children'])) {
        //                 foreach ($child['children'] as $grandchild) {
        //                     $building->accountingAccounts()->create([
        //                         'name' => $grandchild['name'],
        //                         'code' => $grandchild['code'],
        //                         'type' => $grandchild['type'],
        //                         // 'is_locked' => true,
        //                         'parent_id' => $created_child->id,
        //                     ]);
        //                 }
        //             }
        //         }
        //     }
        // }

        // $building = Building::where('name_en', 'atishahr')->first();
        // $units = $building->units()->whereIn('unit_number', [
        //     'A2-09-2096',
        //     'A2-09-2092',
        // ])->get();
        // foreach ($units as $unit) {
        //     if ($unit->charge_debt > 0){
        //         $resident = $unit->renter ?? $unit->owner;
        //         $charge = $unit->charge_fee;
        //         $debt = $unit->charge_debt;
        //         $token = $unit->token;
        //         $resident->notify(
        //             (new ChargeAddedNotfication($charge, $debt, $token, 'شارژ اردیبهشت'))
        //         );
        //     }
        // }

        // $building = Building::where('name_en', 'atishahr')->first();
        // $debtTypes = [
        //     [
        //         'name' => 'شارژ مجتمع',
        //         'receivable_accounting_account_id' => $building->accountingAccounts()->where('code', '102008')->first()->id,
        //         'income_accounting_account_id' => $building->accountingAccounts()->where('code', '602006')->first()->id,
        //     ],
        //     [
        //         'name' => 'دیرکرد شارژ مجتمع',
        //         'receivable_accounting_account_id' => $building->accountingAccounts()->where('code', '102025')->first()->id,
        //         'income_accounting_account_id' => $building->accountingAccounts()->where('code', '602008')->first()->id,
        //     ],
        //     [
        //         'name' => 'اظهار نامه الکترونیک قضایی',
        //         'receivable_accounting_account_id' => $building->accountingAccounts()->where('code', '102028')->first()->id,
        //         'income_accounting_account_id' => $building->accountingAccounts()->where('code', '602009')->first()->id,
        //     ],
        //     [
        //         'name' => 'کمیسیون نقل و انتقال واحد جاری',
        //         'receivable_accounting_account_id' => $building->accountingAccounts()->where('code', '102038')->first()->id,
        //         'income_accounting_account_id' => $building->accountingAccounts()->where('code', '602010')->first()->id,
        //     ],
        //     [
        //         'name' => 'شارژ عمرانی سالانه مالکین',
        //         'receivable_accounting_account_id' => $building->accountingAccounts()->where('code', '102012')->first()->id,
        //         'income_accounting_account_id' => $building->accountingAccounts()->where('code', '603001')->first()->id,
        //     ],
        //     [
        //         'name' => 'کمیسیون نقل و انتقال واحد-عمرانی',
        //         'receivable_accounting_account_id' => $building->accountingAccounts()->where('code', '102036')->first()->id,
        //         'income_accounting_account_id' => $building->accountingAccounts()->where('code', '603002')->first()->id,
        //     ],
        //     [
        //         'name' => 'برچسب خودرو (راه بند)',
        //         'receivable_accounting_account_id' => $building->accountingAccounts()->where('code', '102015')->first()->id,
        //         'income_accounting_account_id' => $building->accountingAccounts()->where('code', '604003')->first()->id,
        //     ],
        //     [
        //         'name' => 'اجاره انباری',
        //         'receivable_accounting_account_id' => $building->accountingAccounts()->where('code', '102022')->first()->id,
        //         'income_accounting_account_id' => $building->accountingAccounts()->where('code', '604017')->first()->id,
        //     ],
        //     [
        //         'name' => 'جریمه پارک خودرو',
        //         'receivable_accounting_account_id' => $building->accountingAccounts()->where('code', '102024')->first()->id,
        //         'income_accounting_account_id' => $building->accountingAccounts()->where('code', '604019')->first()->id,
        //     ],
        //     [
        //         'name' => 'سمپاشی واحد',
        //         'receivable_accounting_account_id' => $building->accountingAccounts()->where('code', '102032')->first()->id,
        //         'income_accounting_account_id' => $building->accountingAccounts()->where('code', '604023')->first()->id,
        //     ],
        //     [
        //         'name' => 'شارژ اثاث کشی',
        //         'receivable_accounting_account_id' => $building->accountingAccounts()->where('code', '102034')->first()->id,
        //         'income_accounting_account_id' => $building->accountingAccounts()->where('code', '604031')->first()->id,
        //     ],
        //     [
        //         'name' => 'بازسازی واحد و تعمیرات مجتمع',
        //         'receivable_accounting_account_id' => $building->accountingAccounts()->where('code', '102035')->first()->id,
        //         'income_accounting_account_id' => $building->accountingAccounts()->where('code', '604030')->first()->id,
        //     ],
        //     [
        //         'name' => 'تخفیف',
        //         'receivable_accounting_account_id' => $building->accountingAccounts()->where('code', '102008')->first()->id,
        //         'income_accounting_account_id' => $building->accountingAccounts()->where('code', '605001')->first()->id,
        //     ],
        // ];

        // foreach ($debtTypes as $debtType) {
        //     $building->debtTypes()->create($debtType);
        // }

        // foreach ($building->invoices as $invoice) {
        //     $invoice->update([
        //         'debt_type_id' => $building->debtTypes()->where('name', 'شارژ مجتمع')->first()->id,
        //     ]);
        // }

        // $building = Building::where('name_en', 'atishahr')->first();
        // $units = $building->units()->whereIn('unit_number', [
        //     'A4-03-4038',
        //     'A5-02-5026',
        //     'A2-03-2032',
        // ])->get();
        // foreach ($units as $unit) {
        //     if ($unit->charge_debt > 0){
        //         $resident = $unit->renter ?? $unit->owner;
        //         $charge = $unit->charge_fee;
        //         $debt = $unit->charge_debt;
        //         $token = $unit->token;
        //         $resident->notify(
        //             (new ChargeAddedNotfication($charge, $debt, $token, 'شارژ اردیبهشت'))
        //         );
        //     }
        // }


        // $buildings = Building::all();

        // foreach ($buildings as $building) {
        //     $units = $building->units;
        //     $this->info('Building: ' . $building->name_en);
        //     $bar = $this->output->createProgressBar(count($units));
        //     foreach ($units as $unit) {
        //         foreach (['resident', 'owner'] as $type) {
        //             try {
        //                 $invoices = $unit->invoices()
        //                     ->where('status', 'paid')
        //                     ->where('is_verified', 1)
        //                     ->where('resident_type', $type)
        //                     ->orderBy('created_at', 'asc')
        //                     ->get();
        //                 foreach ($invoices as $invoice) {
        //                     $invoice->paid_data = [];
        //                     $invoice->paid_amount = 0;
        //                     $invoice->is_paid = 0;
        //                     $invoice->savequietly();
        //                     if ($invoice->amount > 0) {
        //                         $pending_debts = $unit->invoices()
        //                             ->where('amount', '<', 0)
        //                             ->where('status', 'paid')
        //                             ->where('is_verified', 1)
        //                             ->where('created_at', '<=', $invoice->created_at)
        //                             ->where('is_paid', 0)
        //                             ->where('resident_type', $type)
        //                             ->orderBy('created_at', 'asc')
        //                             ->get();
        //                         foreach ($pending_debts as $pending_debt) {
        //                             $deposit_amount = $invoice->amount - $invoice->paid_amount;
        //                             $debt_amount = (-1 * $pending_debt->amount) - $pending_debt->paid_amount;
        //                             if ($deposit_amount >= $debt_amount) {
        //                                 $invoice->paid_amount += $debt_amount;
        //                                 $pending_debt->paid_amount += $debt_amount;
        //                                 $pending_debt->is_paid = 1;
        //                                 $pending_debt->savequietly();
        //                                 $invoice->paid_data = array_merge($invoice->paid_data ?? [], [[
        //                                     'debt_id' => $pending_debt->id,
        //                                     'amount' => round($debt_amount, 1),
        //                                 ]]);
        //                                 $invoice->savequietly();
        //                             } else {
        //                                 $invoice->paid_amount += $deposit_amount;
        //                                 $pending_debt->paid_amount += $deposit_amount;
        //                                 $pending_debt->savequietly();
        //                                 $invoice->paid_data = array_merge($invoice->paid_data ?? [], [[
        //                                     'debt_id' => $pending_debt->id,
        //                                     'amount' => round($deposit_amount, 1),
        //                                 ]]);
        //                                 $invoice->savequietly();
        //                                 break;
        //                             }
        //                         }
        //                         if ($invoice->amount == $invoice->paid_amount) {
        //                             $invoice->is_paid = 1;
        //                             $invoice->savequietly();
        //                         }
        //                     }
        //                     if ($invoice->amount < 0) {
        //                         $pending_deposits = $unit->invoices()
        //                             ->where('status', 'paid')
        //                             ->where('amount', '>', 0)
        //                             ->where('is_verified', 1)
        //                             ->where('created_at', '<=', $invoice->created_at)
        //                             ->where('is_paid', 0)
        //                             ->where('resident_type', $type)
        //                             ->orderBy('created_at', 'asc')
        //                             ->get();
        //                         $debt_amount = (-1 * $invoice->amount) - $invoice->paid_amount;
        //                         foreach ($pending_deposits as $pending_deposit) {
        //                             $deposit_amount = ($pending_deposit->amount - $pending_deposit->paid_amount);
        //                             if ($debt_amount >= $deposit_amount) {
        //                                 $pending_deposit->is_paid = 1;
        //                                 $pending_deposit->paid_amount += $deposit_amount;
        //                                 $pending_deposit->paid_data = array_merge($pending_deposit->paid_data ?? [], [[
        //                                     'debt_id' => $invoice->id,
        //                                     'amount' => round($deposit_amount, 1),
        //                                 ]]);
        //                                 $pending_deposit->savequietly();
        //                                 $invoice->paid_amount += $deposit_amount;
        //                                 $invoice->savequietly();
        //                             } else {
        //                                 $pending_deposit->paid_amount += $debt_amount;
        //                                 $pending_deposit->paid_data = array_merge($pending_deposit->paid_data ?? [], [[
        //                                     'debt_id' => $invoice->id,
        //                                     'amount' => round($debt_amount, 1),
        //                                 ]]);
        //                                 $pending_deposit->savequietly();
        //                                 $invoice->paid_amount += $debt_amount;
        //                                 $invoice->is_paid = 1;
        //                                 $invoice->savequietly();
        //                                 break;
        //                             }
        //                         }
        //                         if ($invoice->amount == -1 * $invoice->paid_amount) {
        //                             $invoice->is_paid = 1;
        //                             $invoice->savequietly();
        //                         }
        //                     }
        //                 }
        //             } catch (\Throwable $th) {
        //                 Log::error($th);
        //             }
        //         }
        //         $bar->advance();
        //     }
        //     $bar->finish();
        //     $this->info('');
        // }

        // $building = Building::where('name_en', 'hshcomplex')->first();
        // $units = $building->units;
        // foreach ($units as $unit) {
        //     $resident = $unit->renter ?? $unit->owner;
        //     $resident->notify(new CustomNotification([
        //         'VAHED' => $unit->unit_number,
        //         'SECTION' => "آگهی‌ها",
        //         'SUBJECT' => "",
        //         'TARGET' => "با امنیت‌خاطر",
        //         'BUILD' => $building->name,
        //     ], 737618));
        // }

        // $units = BuildingUnit::all();
        // $progressBar = $this->output->createProgressBar(count($units));
        // foreach ($units as $unit) {
        //     try {
        //         $unit->resident_debt = $unit->debt('resident');
        //         $unit->owner_debt = $unit->debt('owner');
        //         $unit->charge_debt = $unit->debt();
        //         $unit->saveQuietly();
        //     } catch (\Throwable $th) {
        //         Log::error($th);
        //     }
        //     $progressBar->advance();
        // }
        // $progressBar->finish();

        // AccountingDocument::truncate();
        // AccountingTransaction::truncate();

        // $buildings = Building::where('name_en', 'atishahr')->get();

        // foreach ($buildings as $building) {
        //     try {

        //         $building->accountingTransactions()->forceDelete();
        //         $building->accountingDocuments()->forceDelete();
        //         $document = $building->accountingDocuments()->create([
        //             'building_id' => $building->id,
        //             'description' => 'سند افتتاحیه',
        //             'document_number' => 1,
        //             'amount' => 0,
        //             'created_at' => '2024-03-20 00:00:00',
        //         ]);
        //         $debit_transaction = [
        //             '101001' => 554293512,
        //             '101002' => 1583295489,
        //             '101003' => 1021759734,
        //             '102002' => 300000000,
        //             '102003' => 86191855,
        //             '102008' => 1757754897,
        //             '102012' => 1715402592,
        //             '102024' => 950354,
        //             '102025' => 3348575452,
        //             '102028' => 11688000,
        //             '102036' => 189800000,
        //             '103002' => 162500000,
        //             '502001' => 20838845378,
        //         ];

        //         $credit_transaction = [
        //             '301005' => 686153000,
        //             '301006' => 193862000,
        //             '301039' => 60000000,
        //             '301040' => 668725000,
        //             '301046' => 2845182840,
        //             '301048' => 256300200,
        //             '301057' => 25000000,
        //             '301060' => 400000000,
        //             '301061' => 320000000,
        //             '301086' => 494500000,
        //             '301123' => 100000000,
        //             '302007' => 46440000,
        //             '302010' => 22689545218,
        //             '303001' => 2785349005,
        //         ];

        //         // $document->transactions()->createMany([
        //         //     [
        //         //         'accounting_account_id' => $building->accountingAccounts()->where('code', '101001')->first()->id,
        //         //         'accounting_detail_id' => null,
        //         //         'description' => 'افتتاحیه',
        //         //         'credit' => 0,
        //         //         'debit' => 0,
        //         //         'created_at' => $document->created_at,
        //         //     ],
        //         // ]);

        //         foreach ($debit_transaction as $code => $amount) {
        //             $document->transactions()->create([
        //                 'accounting_account_id' => $building->accountingAccounts()->where('code', $code)->first()->id,
        //                 'accounting_detail_id' => null,
        //                 'description' => 'افتتاحیه',
        //                 'credit' => 0,
        //                 'debit' => $amount,
        //                 'created_at' => $document->created_at,
        //             ]);
        //         }

        //         foreach ($credit_transaction as $code => $amount) {
        //             $document->transactions()->create([
        //                 'accounting_account_id' => $building->accountingAccounts()->where('code', $code)->first()->id,
        //                 'accounting_detail_id' => null,
        //                 'description' => 'افتتاحیه',
        //                 'credit' => $amount,
        //                 'debit' => 0,
        //                 'created_at' => $document->created_at,
        //             ]);
        //         }

        //         $document->update([
        //             'amount' => $document->transactions()->sum('debit'),
        //         ]);
        //     } catch (\Throwable $th) {
        //         Log::error($th);
        //     }
        // }

        // foreach ($buildings as $building) {
        //     try {

        //         $unit_observer = new BuildingUnitObserver();
        //         $deposit_request_observer = new DepositRequestObserver();
        //         $invoice_observer = new InvoiceObserver();
        //         // foreach ($building->units as $unit) {
        //         //     $unit_observer->created($unit);
        //         // }
        //         $progressBar = $this->output->createProgressBar($building->depositRequests()->count());
        //         foreach ($building->depositRequests as $deposit_request) {
        //             $deposit_request_observer->created($deposit_request);
        //             $progressBar->advance();
        //         }
        //         $progressBar->finish();
        //         $progressBar = $this->output->createProgressBar($building->invoices()->orderBy('created_at', 'asc')
        //             ->where('created_at', '>=', '2024-03-20 00:00:00')
        //             ->count());
        //         foreach ($building->invoices()->orderBy('created_at', 'asc')
        //             ->where('created_at', '>=', '2024-03-20 00:00:00')
        //             ->get() as $invoice) {
        //             $invoice_observer->created($invoice);
        //             $progressBar->advance();
        //         }
        //         $progressBar->finish();
        //     } catch (\Throwable $th) {
        //         Log::error($th);
        //     }
        //     // $progressBar->advance();
        // }
        // $progressBar->finish();

        // $buildings = Building::whereNot('name_en', 'atishahr')->get();

        // foreach ($buildings as $building) {
        //     $building->invoices()->update([
        //         'resident_type' => 'resident',
        //     ]);
        //     $units = $building->units;
        //     $this->info('Building: ' . $building->name_en);
        //     $bar = $this->output->createProgressBar(count($units));
        //     foreach ($units as $unit) {
        //         foreach (['resident', 'owner'] as $type) {
        //             try {
        //                 $invoices = $unit->invoices()
        //                     ->where('status', 'paid')
        //                     ->where('is_verified', 1)
        //                     ->where('resident_type', $type)
        //                     ->orderBy('created_at', 'asc')
        //                     ->get();
        //                 foreach ($invoices as $invoice) {
        //                     $invoice->paid_data = [];
        //                     $invoice->paid_amount = 0;
        //                     $invoice->is_paid = 0;
        //                     $invoice->savequietly();
        //                     if ($invoice->amount > 0) {
        //                         $pending_debts = $unit->invoices()
        //                             ->where('amount', '<', 0)
        //                             ->where('status', 'paid')
        //                             ->where('is_verified', 1)
        //                             ->where('created_at', '<=', $invoice->created_at)
        //                             ->where('is_paid', 0)
        //                             ->where('resident_type', $type)
        //                             ->orderBy('created_at', 'asc')
        //                             ->get();
        //                         foreach ($pending_debts as $pending_debt) {
        //                             $deposit_amount = $invoice->amount - $invoice->paid_amount;
        //                             $debt_amount = (-1 * $pending_debt->amount) - $pending_debt->paid_amount;
        //                             if ($deposit_amount >= $debt_amount) {
        //                                 $invoice->paid_amount += $debt_amount;
        //                                 $pending_debt->paid_amount += $debt_amount;
        //                                 $pending_debt->is_paid = 1;
        //                                 $pending_debt->savequietly();
        //                                 $invoice->paid_data = array_merge($invoice->paid_data ?? [], [[
        //                                     'debt_id' => $pending_debt->id,
        //                                     'amount' => round($debt_amount, 1),
        //                                 ]]);
        //                                 $invoice->savequietly();
        //                             } else {
        //                                 $invoice->paid_amount += $deposit_amount;
        //                                 $pending_debt->paid_amount += $deposit_amount;
        //                                 $pending_debt->savequietly();
        //                                 $invoice->paid_data = array_merge($invoice->paid_data ?? [], [[
        //                                     'debt_id' => $pending_debt->id,
        //                                     'amount' => round($deposit_amount, 1),
        //                                 ]]);
        //                                 $invoice->savequietly();
        //                                 break;
        //                             }
        //                         }
        //                         if ($invoice->amount == $invoice->paid_amount) {
        //                             $invoice->is_paid = 1;
        //                             $invoice->savequietly();
        //                         }
        //                     }
        //                     if ($invoice->amount < 0) {
        //                         $pending_deposits = $unit->invoices()
        //                             ->where('status', 'paid')
        //                             ->where('amount', '>', 0)
        //                             ->where('is_verified', 1)
        //                             ->where('created_at', '<=', $invoice->created_at)
        //                             ->where('is_paid', 0)
        //                             ->where('resident_type', $type)
        //                             ->orderBy('created_at', 'asc')
        //                             ->get();
        //                         $debt_amount = (-1 * $invoice->amount) - $invoice->paid_amount;
        //                         foreach ($pending_deposits as $pending_deposit) {
        //                             $deposit_amount = ($pending_deposit->amount - $pending_deposit->paid_amount);
        //                             if ($debt_amount >= $deposit_amount) {
        //                                 $pending_deposit->is_paid = 1;
        //                                 $pending_deposit->paid_amount += $deposit_amount;
        //                                 $pending_deposit->paid_data = array_merge($pending_deposit->paid_data ?? [], [[
        //                                     'debt_id' => $invoice->id,
        //                                     'amount' => round($deposit_amount, 1),
        //                                 ]]);
        //                                 $pending_deposit->savequietly();
        //                                 $invoice->paid_amount += $deposit_amount;
        //                                 $invoice->savequietly();
        //                             } else {
        //                                 $pending_deposit->paid_amount += $debt_amount;
        //                                 $pending_deposit->paid_data = array_merge($pending_deposit->paid_data ?? [], [[
        //                                     'debt_id' => $invoice->id,
        //                                     'amount' => round($debt_amount, 1),
        //                                 ]]);
        //                                 $pending_deposit->savequietly();
        //                                 $invoice->paid_amount += $debt_amount;
        //                                 $invoice->is_paid = 1;
        //                                 $invoice->savequietly();
        //                                 break;
        //                             }
        //                         }
        //                         if ($invoice->amount == -1 * $invoice->paid_amount) {
        //                             $invoice->is_paid = 1;
        //                             $invoice->savequietly();
        //                         }
        //                     }
        //                 }
        //             } catch (\Throwable $th) {
        //                 Log::error($th);
        //             }
        //         }
        //         $bar->advance();
        //     }
        //     $bar->finish();
        //     $this->info('');
        // }

        // $units = BuildingUnit::all();
        // $progressBar = $this->output->createProgressBar(count($units));
        // foreach ($units as $unit) {
        //     try {
        //         $unit->resident_debt = $unit->debt('resident');
        //         $unit->owner_debt = $unit->debt('owner');
        //         $unit->charge_debt = $unit->debt();
        //         $unit->saveQuietly();
        //     } catch (\Throwable $th) {
        //         Log::error($th);
        //     }
        //     $progressBar->advance();
        // }
        // $progressBar->finish();

        // $buildings = Building::whereIn('name_en', ['jamtower', 'atishahr'])->get();
        // foreach ($buildings as $building) {
        //     $this->info('Building: ' . $building->name_en);
        //     $units = $building->units;
        //     $progressBar = $this->output->createProgressBar(count($units));
        //     foreach ($units as $unit) {
        //         $resident = $unit->renter ?? $unit->owner;
        //         $resident->notify(new CustomNotification([
        //             'USER' => '',
        //             'NUMBER' => '+989334691701',
        //             'APP' => "تلگرام",
        //             'HOUR' => "9 تا 14",
        //         ], 716676));
        //         $progressBar->advance();
        //     }
        //     $progressBar->finish();
        // }

        // $buildings = Building::where('name_en', 'atishahr')->get();

        // foreach ($buildings as $building) {
        //     try {

        //         $building->accountingTransactions()->forceDelete();
        //         $building->accountingDocuments()->forceDelete();
        //         $document = $building->accountingDocuments()->create([
        //             'building_id' => $building->id,
        //             'description' => 'سند افتتاحیه',
        //             'document_number' => 1,
        //             'amount' => 0,
        //             'created_at' => '2024-03-20 00:00:00',
        //         ]);
        //         $debit_transaction = [
        //             '101001' => 554293512,
        //             '101002' => 1583295489,
        //             '101003' => 1021759734,
        //             '102002' => 300000000,
        //             '102003' => 86191855,
        //             '102008' => 1757754897,
        //             '102012' => 1715402592,
        //             '102024' => 950354,
        //             '102025' => 3348575452,
        //             '102028' => 11688000,
        //             '102036' => 189800000,
        //             '103002' => 162500000,
        //             '502001' => 20838845378,
        //         ];

        //         $credit_transaction = [
        //             '301005' => 686153000,
        //             '301006' => 193862000,
        //             '301039' => 60000000,
        //             '301040' => 668725000,
        //             '301046' => 2845182840,
        //             '301048' => 256300200,
        //             '301057' => 25000000,
        //             '301060' => 400000000,
        //             '301061' => 320000000,
        //             '301086' => 494500000,
        //             '301123' => 100000000,
        //             '302007' => 46440000,
        //             '302010' => 22689545218,
        //             '303001' => 2785349005,
        //         ];

        //         // $document->transactions()->createMany([
        //         //     [
        //         //         'accounting_account_id' => $building->accountingAccounts()->where('code', '101001')->first()->id,
        //         //         'accounting_detail_id' => null,
        //         //         'description' => 'افتتاحیه',
        //         //         'credit' => 0,
        //         //         'debit' => 0,
        //         //         'created_at' => $document->created_at,
        //         //     ],
        //         // ]);

        //         foreach ($debit_transaction as $code => $amount) {
        //             $document->transactions()->create([
        //                 'accounting_account_id' => $building->accountingAccounts()->where('code', $code)->first()->id,
        //                 'accounting_detail_id' => null,
        //                 'description' => 'افتتاحیه',
        //                 'credit' => 0,
        //                 'debit' => $amount,
        //                 'created_at' => $document->created_at,
        //             ]);
        //         }

        //         foreach ($credit_transaction as $code => $amount) {
        //             $document->transactions()->create([
        //                 'accounting_account_id' => $building->accountingAccounts()->where('code', $code)->first()->id,
        //                 'accounting_detail_id' => null,
        //                 'description' => 'افتتاحیه',
        //                 'credit' => $amount,
        //                 'debit' => 0,
        //                 'created_at' => $document->created_at,
        //             ]);
        //         }

        //         $document->update([
        //             'amount' => $document->transactions()->sum('debit'),
        //         ]);
        //     } catch (\Throwable $th) {
        //         Log::error($th);
        //     }
        // }

        // foreach ($buildings as $building) {
        //     try {
        //         $deposit_request_observer = new DepositRequestObserver();
        //         $invoice_observer = new InvoiceObserver();
        //         $progressBar = $this->output->createProgressBar($building->depositRequests()->orderBy('created_at', 'asc')
        //             ->where('created_at', '>=', '2024-03-20 00:00:00')
        //             ->count());
        //         foreach ($building->depositRequests()->orderBy('created_at', 'asc')
        //             ->where('created_at', '>=', '2024-03-20 00:00:00')->get() as $deposit_request) {
        //             $deposit_request_observer->created($deposit_request);
        //             $progressBar->advance();
        //         }
        //         $progressBar->finish();
        //         $progressBar = $this->output->createProgressBar($building->invoices()->orderBy('created_at', 'asc')
        //             ->where('created_at', '>=', '2024-03-20 00:00:00')
        //             ->count());
        //         // $invoice->paid_data = [];
        //         // $invoice->paid_amount = 0;
        //         // $invoice->is_paid = 0;
        //         $building->invoices()->orderBy('created_at', 'asc')
        //             ->where('created_at', '>=', '2024-03-20 00:00:00')->update([
        //                 'paid_data' => [],
        //                 'paid_amount' => 0,
        //                 'is_paid' => 0,
        //             ]);
        //         foreach ($building->invoices()->orderBy('created_at', 'asc')
        //             ->where('created_at', '>=', '2024-03-20 00:00:00')
        //             ->get() as $invoice) {
        //             $invoice_observer->created($invoice);
        //             $progressBar->advance();
        //         }
        //         $progressBar->finish();
        //     } catch (\Throwable $th) {
        //         Log::error($th);
        //     }
        //     // $progressBar->advance();
        // }
        // $progressBar->finish();

        // $buildings = Building::all();
        // foreach ($buildings as $building) {
        //     $this->info('Building: ' . $building->name_en);
        //     foreach ($building->modules as $module) {
        //         if ($module->pivot->ends_at == null) {
        //             $module->pivot->update([
        //                 'ends_at' => $building->plan_expires_at
        //             ]);
        //         }
        //     }
        // }

        // $building = Building::where('name_en', 'hshcomplex')->first();
        // $units = $building->units;
        // foreach ($units as $unit) {
        //     $charge_fee = $unit->charge_fee;
        //     if ($unit->charge_debt > 0) {
        //         $resident = $unit->renter ?? $unit->owner;
        //         $resident->notify(
        //             (new ChargeAddedNotfication($charge_fee, $unit->charge_debt, $unit->token, 'شارژ اردیبهشت'))
        //         );
        //     }
        // }

        // $users = User::whereIn('mobile', [
        //     '09124218398',
        //     '09125052364',
        //     // '09031799722'
        // ])->get();
        // foreach ($users as $user) {
        //     $user->notify(new CustomNotification([
        //         'NAME' => 'مدیریت نامحدود واحد',
        //         'DAYS' => '2',
        //     ], 382047));
        // }

        // $buildings = Building::whereIn('name_en', ['atishahr'])->get();
        // foreach ($buildings as $building) {
        //     $this->info('Building: ' . $building->name_en);
        //     $units = $building->units;
        //     $progressBar = $this->output->createProgressBar(count($units));
        //     foreach ($units as $unit) {
        //         $resident = $unit->renter ?? $unit->owner;
        //         $resident->notify(new CustomNotification([
        //             'USER' => Str::limit('واحد ' . $unit->unit_number, 25),
        //             'CH' => 'کانال تلگرام ما',
        //             'MEMBER' => "ساکنین",
        //         ], 973816));
        //         $progressBar->advance();
        //     }
        //     $progressBar->finish();
        // }

        // $buildingUnits = BuildingUnit::all();
        // $progressBar = $this->output->createProgressBar(count($buildingUnits));
        // foreach ($buildingUnits as $buildingUnit) {
        //     try {
        //         $buildingUnit->resident_debt = $buildingUnit->debt('resident');
        //         $buildingUnit->owner_debt = $buildingUnit->debt('owner');
        //         $buildingUnit->charge_debt = $buildingUnit->debt();
        //         $buildingUnit->saveQuietly();
        //     } catch (\Throwable $th) {
        //         Log::error($th);
        //     }
        //     $progressBar->advance();
        // }
        // $progressBar->finish();


        // $building = Building::where('name_en', 'hshcomplex')->first();
        // $units = $building->units;
        // foreach ($units as $unit) {
        //     $charge_fee = $unit->charge_fee;
        //     if ($unit->charge_debt > 0) {
        //         $resident = $unit->renter ?? $unit->owner;
        //         $resident->notify(
        //             (new ChargeAddedNotfication($charge_fee, $unit->charge_debt, $unit->token, 'شارژ خرداد'))
        //         );
        //     }
        // }

        // $buildings = Building::all();
        // $progressBar = $this->output->createProgressBar(count($buildings));

        // foreach ($buildings as $building) {
        //     try {

        //         $debtTypes = [
        //             'شارژ جاری',
        //             'شارژ عمرانی',
        //             'جریمه دیرکرد',
        //             'تخفیف',
        //         ];

        //         foreach ($debtTypes as $debtType) {
        //             $building->debtTypes()->firstOrCreate([
        //                 'name' => $debtType,
        //             ], [
        //                 'receivable_accounting_account_id' => $building->accountingAccounts()->where('code', '1305')->first()->id,
        //                 'income_accounting_account_id' => $building->accountingAccounts()->where('code', '6103')->first()->id,
        //             ]);
        //         }
        //     } catch (\Throwable $th) {
        //         Log::error($th);
        //     }
        //     $progressBar->advance();
        // }

        // $progressBar->finish();

        // $users = User::all();
        // $progressBar = $this->output->createProgressBar(count($users));
        // $file = fopen(storage_path('users.csv'), 'w');
        // fputcsv($file, ['name', 'mobile']);
        // foreach ($users as $user) {
        //     if ($user->role == 'user' && !$user->building_units) {
        //         fputcsv($file, [$user->full_name, $user->mobile]);
        //     }
        //     $progressBar->advance();
        // }
        // fclose($file);
        // $progressBar->finish();


        // $buildings = Building::whereIn('name_en', ['hshcomplex'])->get();
        // foreach ($buildings as $building) {
        //     $file = fopen(storage_path($building->name_en . '.csv'), 'w');
        //     // utf 8 file
        //     fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        //     fputcsv($file, ['واحد', 'نام', 'موبایل', 'بدهی']);
        //     $this->info('Building: ' . $building->name_en);
        //     $units = $building->units;
        //     $progressBar = $this->output->createProgressBar(count($units));
        //     foreach ($units as $unit) {
        //         $resident = $unit->renter ?? $unit->owner;
        //         $invoices = $unit->invoices()
        //             ->where('status', 'paid')
        //             ->where('is_verified', 1)
        //             ->where('created_at', '<=', '2024-07-21 23:59:59');
        //         $debt = -1 * $invoices->sum('amount');
        //         fputcsv($file, [
        //             $unit->unit_number,
        //             $resident->full_name,
        //             $resident->mobile,
        //             $debt,
        //         ]);
        //         $progressBar->advance();
        //     }
        //     $progressBar->finish();
        // }

        // Factor::truncate();

        // $building = Building::where('name_en', 'hshcomplex')->first();
        // $start_date = '1402-04-01 00:00:00';

        // $json_file = file_get_contents(resource_path('hsh-result.json'));
        // $sms_counts = json_decode($json_file, true);

        // for ($i=1; $i <= 12; $i++) {
        //     $date = Jalalian::fromFormat('Y-m-d H:i:s', $start_date)->addMonths($i)->addDays(-1);
        //     $start_of_month = $date->addDays(1)->subMonths(1);
        //     $end_of_month = $date;
        //     $factor = new Factor();
        //     $factor->building_id = $building->id;
        //     $factor->created_at = $end_of_month->toCarbon();
        //     $factor->updated_at = $end_of_month->toCarbon();
        //     $factor->customer_name = $building->name;
        //     $factor->address = $building->mainBuildingManagers->first()->details->address;
        //     $factor->postal_code = $building->mainBuildingManagers->first()->details->postal_code;
        //     $factor->city = $building->mainBuildingManagers->first()->details->city;
        //     $factor->due_date = $end_of_month->addDays(10)->toCarbon();
        //     $factor->has_vat = 1;
        //     $factor->vat_percent = $end_of_month->getYear() == 1402 ? 9 : 10;
        //     $items = [
        //         [
        //             'name' => 'خدمات حسابداری پیشرفته (شهرک)',
        //             'quantity' => 6,
        //             'price' => 60000000,
        //             'discount' => 0,
        //         ]
        //     ];

        //     $sms_count = 0;

        //     for ($j=1; $j <= $start_of_month->getMonthDays(); $j++) {
        //         $key = $start_of_month->toCarbon()->format('Y-m-d');
        //         if (isset($sms_counts[$key])) {
        //             $sms_count += $sms_counts[$key];
        //         }
        //     }
        //     if ($sms_count > 0) {
        //         $items[] = [
        //             'name' => 'پیامک سیستمی',
        //             'quantity' => $sms_count * 2,
        //             'price' => 2240,
        //             'discount' => 0,
        //         ];
        //     }

        //     $factor->items = $items;

        //     $factor->save();

        //     foreach ($factor->items as $item) {
        //         $factor->amount += $item->price * $item->quantity;
        //     }
        //     $vat = round($factor->amount * $factor->vat_percent / 100);
        //     $factor->amount = $factor->amount + $vat;
        //     $factor->save();
        // }


        // $building = Building::where('name_en', 'hshcomplex')->first();
        // $factors = $building->factors()->get();

        // foreach ($factors as $factor) {
        //     # change from 60000000 to 70000000
        //     $items = $factor->items;
        //     $items[0]->price = 80000000;
        //     $factor->items = $items;
        //     $factor->amount = 0;
        //     foreach ($factor->items as $item) {
        //         $factor->amount += $item->price * $item->quantity;
        //     }
        //     $vat = round($factor->amount * $factor->vat_percent / 100);
        //     $factor->amount = $factor->amount + $vat;
        //     $factor->timestamps = false;
        //     $factor->save();
        // }

        // $building = Building::where('name_en', 'hshcomplex')->first();

        // $online_payments = $building->invoices()
        //     ->where('serviceable_type', BuildingUnit::class)
        //     ->whereNot('payment_method', 'cash')
        //     ->where('status', 'paid')
        //     ->where('is_verified', true)
        //     ->where('amount', '>', 0)
        //     ->cursor();

        // $file = fopen(storage_path('hsh_online_payments.csv'), 'w');
        // fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        // fputcsv($file, ['شماره واحد', 'مبلغ پرداختی', 'توضیحات', 'تاریخ', 'درگاه', 'کد پیگیری']);

        // foreach ($online_payments as $online_payment) {
        //     $payment_method = '';
        //     switch ($online_payment->payment_method) {
        //         case 'App\Helpers\Pasargad':
        //             $payment_method = 'پاسارگاد';
        //             break;
        //         case 'Shetabit\Multipay\Drivers\Pasargad\Pasargad':
        //             $payment_method = 'پاسارگاد';
        //             break;
        //         case 'App\Helpers\SEP':
        //             $payment_method = 'سامان';
        //             break;
        //         case 'Shetabit\Multipay\Drivers\Sepehr\Sepehr':
        //             $payment_method = 'صادرات';
        //             break;
        //         case 'wallet':
        //             $payment_method = 'کیف پول';
        //             break;

        //         default:
        //             # code...
        //             break;
        //     }
        //     fputcsv($file, [
        //         $online_payment->unit->unit_number,
        //         $online_payment->amount * 10,
        //         $online_payment->description,
        //         Jalalian::fromCarbon($online_payment->created_at)->format('Y/m/d'),
        //         $payment_method,
        //         $online_payment->payment_tracenumber,
        //     ]);
        // }

        // fclose($file);

        // $withdrawals = $building->depositRequests()
        //     ->where('status', 'accepted')
        //     // ->whereBetween('created_at', [$start, $end])
        //     ->cursor();

        // $file = fopen(storage_path('hsh_withdrawals.csv'), 'w');
        // fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        // fputcsv($file, ['مبلغ', 'حساب', 'توضیحات', 'تاریخ']);

        // foreach ($withdrawals as $withdrawal) {
        //     fputcsv($file, [
        //         $withdrawal->amount * 10,
        //         $withdrawal->sheba,
        //         $withdrawal->description,
        //         Jalalian::fromCarbon($withdrawal->created_at)->format('Y/m/d'),
        //     ]);
        // }

        // fclose($file);

        // $factors = $building->factors()
        //     ->cursor();

        // $file = fopen(storage_path('hsh_factors.csv'), 'w');
        // fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        // fputcsv($file, ['مبلغ', 'لینک فاکتور', 'تاریخ']);

        // foreach ($factors as $factor) {
        //     fputcsv($file, [
        //         $factor->amount,
        //         route('v1.public.factors.view', ['token' => $factor->token]),
        //         Jalalian::fromCarbon($factor->created_at)->format('Y/m/d'),
        //     ]);
        // }

        // fclose($file);

        // // put all file into a zip file
        // $zip = new ZipArchive();
        // $zip->open(storage_path('hsh.zip'), ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // $zip->addFile(storage_path('hsh_online_payments.csv'), 'hsh_online_payments.csv');
        // $zip->addFile(storage_path('hsh_withdrawals.csv'), 'hsh_withdrawals.csv');
        // $zip->addFile(storage_path('hsh_factors.csv'), 'hsh_factors.csv');

        // $zip->close();

        // $this->info('Done!');

        // $building = Building::where('name_en', 'Kasra')->first();
        // try {
        //     $modules = Module::whereIn('slug', ['base-inf', 'accounting-basic', 'accounting-advanced-1', 'accounting-advanced-2', 'stocks', 'reserve-and-poll', 'fine-and-reward'])->get();
        //     $building->modules()->attach($modules, [
        //         'starts_at' => now(),
        //         'ends_at' => now()->addDays(30)->endOfDay(),
        //         'price' => 0,
        //     ]);
        // } catch (\Throwable $th) {
        //     $this->error($th);
        // }

        // $building = Building::where('name_en', 'Mahsa')->first();
        // try {
        //     foreach ($building->modules as $module) {
        //         $module->pivot->update([
        //             'ends_at' => now()->subDay(),
        //         ]);
        //     }
        //     $modules = Module::whereIn('slug', ['base-inf', 'accounting-basic', 'accounting-advanced-1', 'accounting-advanced-2', 'stocks', 'reserve-and-poll', 'fine-and-reward'])->get();
        //     $building->modules()->attach($modules, [
        //         'starts_at' => now(),
        //         'ends_at' => now()->addDays(150)->endOfDay(),
        //         'price' => 0,
        //     ]);
        // } catch (\Throwable $th) {
        //     $this->error($th);
        // }

        // $user = User::find(1);
        // $user->notify(new CustomFCMNotification());

        // $inopay = new Inopay();
        // dd($inopay->getBalance(Building::first()));

        // select user where role is user and dont have building unit
        // $users = User::where('role', 'user')->whereDoesntHave('building_units', function ($query) {
        //     $query->withTrashed();
        // })->get();
        // $progressBar = $this->output->createProgressBar(count($users));
        // $file = fopen(storage_path('users_wo_unit.csv'), 'w');
        // fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        // fputcsv($file, ['name', 'mobile']);
        // foreach ($users as $user) {
        //     fputcsv($file, [$user->full_name, $user->mobile]);
        //     $progressBar->advance();
        // }
        // fclose($file);

        // $progressBar->finish();

        // // send file to telegram bot with toke : 5825655479:AAG6pSu1qQC5f2ylNpBnblEUUPpjbeGP-Mw

        // Http::attach('document', file_get_contents(storage_path('users_wo_unit.csv')), 'users_wo_unit.csv')
        //     ->post('https://api.telegram.org/bot5825655479:AAG6pSu1qQC5f2ylNpBnblEUUPpjbeGP-Mw/sendDocument', [
        //         'chat_id' => '319601517',
        //         'caption' => 'کاربرانی که واحد مسکونی ندارند',
        //     ]);

        // $buildings = Building::whereDoesntHave('units')->get();
        // $progressBar = $this->output->createProgressBar(count($buildings));
        // $file = fopen(storage_path('buildings_wo_units.csv'), 'w');
        // fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        // fputcsv($file, ['building', 'name', 'mobile']);
        // foreach ($buildings as $building) {
        //     $manager = $building->mainBuildingManagers->first();
        //     if (!$manager) {
        //         continue;
        //     }
        //     fputcsv($file, [$building->name, $manager->full_name, $manager->mobile]);
        //     $progressBar->advance();
        // }
        // fclose($file);

        // $progressBar->finish();

        // // send file to telegram bot with toke : 5825655479:AAG6pSu1qQC5f2ylNpBnblEUUPpjbeGP-Mw

        // Http::attach('document', file_get_contents(storage_path('buildings_wo_units.csv')), 'buildings_wo_units.csv')
        //     ->post('https://api.telegram.org/bot5825655479:AAG6pSu1qQC5f2ylNpBnblEUUPpjbeGP-Mw/sendDocument', [
        //         'chat_id' => '319601517',
        //         'caption' => 'ساختمان هایی که واحد مسکونی ندارند',
        //     ]);

        // $buildings = Building::where('id', '>', 35)->get();
        // $buildings = Building::where('name_en', '=', 'saviz')->get();

        // foreach ($buildings as $building) {
        //     $manager = $building->mainBuildingManagers->first();
        //     if (!$manager) {
        //         continue;
        //     }
        //     foreach ($building->units as $unit) {
        //         $resident = $unit->renter ?? $unit->owner;
        //         $resident->notify(new CustomNotification([
        //             'USER' => $resident->full_name == ' ' ? 'کاربر' : $resident->full_name,
        //             'SECTION' => 'شارژپل',
        //             'MANAGER' => $manager->full_name,
        //         ], 878597));
        //     }
        // }


        // $invoices = Invoice::where('amount', '>', 0)->where('serviceable_type', 'App\Models\Commission')->whereBetween('created_at', ['2024-06-21 00:00:00', '2024-09-21 23:59:59'])->get();
        // $progressBar = $this->output->createProgressBar(count($invoices));
        // $file = fopen(storage_path('invoices.csv'), 'w');
        // fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
        // fputcsv($file, ['amount', 'description', 'created_at']);
        // foreach ($invoices as $invoice) {
        //     fputcsv($file, [$invoice->amount * 10, $invoice->description, Jalalian::fromCarbon($invoice->created_at)->format('Y/m/d')]);
        //     $progressBar->advance();
        // }
        // fclose($file);

        // $progressBar->finish();

        // Http::attach('document', file_get_contents(storage_path('invoices.csv')), 'invoices.csv')
        //     ->post('https://api.telegram.org/bot5825655479:AAG6pSu1qQC5f2ylNpBnblEUUPpjbeGP-Mw/sendDocument', [
        //         'chat_id' => '319601517',
        //         'caption' => 'کمیسیون تابستان',
        //     ]);

        // AccountingDetail::where('name', 'صندوق شارژپل')->update([
        //     'is_locked' => 1,
        // ]);

        // $buildings = Building::all();
        // foreach ($buildings as $building) {
        //     if (!$building->accountingDetails()->where('name', 'صندوق ساختمان')->exists()) {
        //         $max_code = $building->accountingDetails()->max('code') ?? 100000;
        //         $code = $max_code + 1;
        //         $building->accountingDetails()->create([
        //             'name' => 'صندوق ساختمان',
        //             'code' => $code,
        //             'type' => 'cash',
        //             'is_locked' => 0,
        //         ]);
        //     }
        // }

        // $filename = 'users.csv';

        // $users = User::all();
        // $progressBar = $this->output->createProgressBar(count($users));
        // $file = fopen(storage_path($filename), 'w');
        // fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
        // fputcsv($file, ['name', 'mobile']);
        // foreach ($users as $user) {
        //     $progressBar->advance();

        //     $ok = true;

        //     if ($user->role == 'user') {
        //         foreach ($user->building_units()->withTrashed()->get() as $unit) {
        //             if ($unit->building && ($unit->building->name_en == 'hshcomplex' || $unit->building->name_en == 'atishahr')) {
        //                 $this->info($user->mobile);
        //                 $ok = false;
        //             }
        //         }
        //     }
        //     if ($user->role == 'building_manager') {
        //         if (BuildingManager::where('id', $user->id)->whereHas('building', function ($query) {
        //             $query->where('name_en', 'hshcomplex')->orWhere('name_en', 'atishahr');
        //         })->exists()) {
        //             $this->info($user->mobile);
        //             $ok = false;
        //         }
        //     }

        //     if ($ok) {
        //         fputcsv($file, [$user->full_name, $user->mobile]);
        //     }
        // }
        // fclose($file);

        // $progressBar->finish();

        // send file to telegram bot with toke : 5825655479:AAG6pSu1qQC5f2ylNpBnblEUUPpjbeGP-Mw

        // Http::attach('document', file_get_contents(storage_path($filename)), $filename)
        //     ->post('https://api.telegram.org/bot5825655479:AAG6pSu1qQC5f2ylNpBnblEUUPpjbeGP-Mw/sendDocument', [
        //         'chat_id' => '319601517',
        //         'caption' => 'تمام کاربران',
        //     ]);


        // Mail::to([ 'arcenciel.ir@gmail.com', 'saman.moayeri@gmail.com'])->send(
        //     new CustomMail(
        //         'تست ایمیل : ',
        //         " تست ایمیل"
        //     )
        // );

        // $users = User::whereColumn('created_at', '!=', 'updated_at')->get();
        // $progressBar = $this->output->createProgressBar(count($users));
        // foreach ($users as $user) {
        //     $user->update([
        //         'last_login_at' => $user->updated_at,
        //     ]);
        //     $progressBar->advance();
        // }

        // $progressBar->finish();

        // $buildings = Building::all();
        // $progressBar = $this->output->createProgressBar(count($buildings));

        // $filename = 'building_w_units.csv';
        // $file = fopen(storage_path($filename), 'w');
        // fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
        // fputcsv($file, ['building_name', 'unit_count', 'actual_unit_count', 'date', 'manager_name', 'manager_mobile']);

        // $filename2 = 'building_wo_units.csv';
        // $file2 = fopen(storage_path($filename2), 'w');
        // fprintf($file2, chr(0xEF) . chr(0xBB) . chr(0xBF));
        // fputcsv($file2, ['building_name', 'unit_count', 'actual_unit_count', 'date', 'manager_name', 'manager_mobile']);
        // foreach ($buildings as $building) {
        //     $manager = $building->mainBuildingManagers->first();
        //     if (!$manager) {
        //         continue;
        //     }
        //     if ($building->units()->exists()) {
        //         fputcsv($file, [$building->name, $building->unit_count, $building->units()->count(), Jalalian::fromCarbon($building->created_at)->format('Y/m/d'), $manager->full_name, $manager->mobile]);
        //     }else {
        //         fputcsv($file2, [$building->name, $building->unit_count, $building->units()->count(), Jalalian::fromCarbon($building->created_at)->format('Y/m/d'), $manager->full_name, $manager->mobile]);
        //     }
        // }

        // fclose($file);
        // fclose($file2);

        // $progressBar->finish();

        // // send file to telegram bot with toke : 5825655479:AAG6pSu1qQC5f2ylNpBnblEUUPpjbeGP-Mw
        // Http::attach('document', file_get_contents(storage_path($filename)), $filename)
        //     ->post('https://api.telegram.org/bot5825655479:AAG6pSu1qQC5f2ylNpBnblEUUPpjbeGP-Mw/sendDocument', [
        //         'chat_id' => '319601517',
        //         'caption' => 'ساختمان هایی که واحد مسکونی دارند',
        //     ]);

        // Http::attach('document', file_get_contents(storage_path($filename2)), $filename2)
        //     ->post('https://api.telegram.org/bot5825655479:AAG6pSu1qQC5f2ylNpBnblEUUPpjbeGP-Mw/sendDocument', [
        //         'chat_id' => '319601517',
        //         'caption' => 'ساختمان هایی که واحد مسکونی ندارند',
        //     ]);

        // $units = Building::find(27)->units;
        // $progressBar = $this->output->createProgressBar(count($units));
        // foreach ($units as $unit) {
        //     try {
        //         $unit->resident_debt = $unit->debt('resident');
        //         $unit->owner_debt = $unit->debt('owner');
        //         $unit->charge_debt = $unit->debt();
        //         $unit->saveQuietly();
        //     } catch (\Throwable $th) {
        //         Log::error($th);
        //     }
        //     $progressBar->advance();
        // }
        // $progressBar->finish();

        // $buildings = Building::all();
        // $progressBar = $this->output->createProgressBar(count($buildings));

        // foreach ($buildings as $building) {
        //     try {

        //         $debtTypes = [
        //             'شارژ جاری',
        //             'شارژ عمرانی',
        //             'جریمه دیرکرد',
        //             'تخفیف',
        //             'اجاره',
        //         ];

        //         foreach ($debtTypes as $debtType) {
        //             $building->debtTypes()->firstOrCreate([
        //                 'name' => $debtType,
        //             ], [
        //                 'receivable_accounting_account_id' => $building->accountingAccounts()->where('code', '1305')->first()->id,
        //                 'income_accounting_account_id' => $building->accountingAccounts()->where('code', '6103')->first()->id,
        //             ]);
        //         }
        //     } catch (\Throwable $th) {
        //         Log::error($th);
        //     }
        //     $progressBar->advance();
        // }

        // $progressBar->finish();

        // $building = Building::find(27);
        // $total_online_payment = $building->invoices()
        //     ->where('serviceable_type', BuildingUnit::class)
        //     ->whereNot('payment_method', 'cash')
        //     ->where('status', 'paid')
        //     ->where('is_verified', true)
        //     ->where('amount', '>', 0)
        //     ->sum('amount');

        // $total_commission = $building->invoices()
        //     ->where('serviceable_type', Commission::class)
        //     ->where('status', 'paid')
        //     ->where('is_verified', true)
        //     ->where('amount', '>', 0)
        //     ->sum('amount');

        // $total_deposit_request = $building->depositRequests()
        //     ->where('status', 'accepted')
        //     ->sum('amount');

        // $this->info('Building: ' . $building->name_en);
        // $this->info('Total Online Payment: ' . $total_online_payment);
        // $this->info('Total Deposit Request: ' . $total_deposit_request);
        // $this->info('Total Commission: ' . $total_commission);


        // $numbers = [];

        // $units = $building->units()->withTrashed()->get();

        // $this->info('Total Units: ' . count($units));

        // foreach ($units as $unit) {
        //     $resident = $unit->residentsWithTrashed()->first();
        //     if ($resident) {
        //         $numbers[] = $resident->mobile;
        //     }
        // }

        // // unique
        // $numbers = array_unique($numbers);

        // foreach ($numbers as $number) {
        //     $this->info($number);
        //     $user = User::where('mobile', $number)->first();
        //     if ($user) {
        //         $user->notify(new CustomNotification([
        //             'name' => $user->full_name ?? ' ',
        //             'reason' => 'عدم تمدید',
        //         ], 169304));
        //     }
        // }

        // $users = User::all();

        // $progressBar = $this->output->createProgressBar(count($users));

        // $file = fopen(storage_path('users.csv'), 'w');
        // fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
        // fputcsv($file, ['name', 'mobile']);
        // foreach ($users as $user) {
        //     $progressBar->advance();
        //     foreach ($user->building_units as $unit) {
        //         if ($unit->building && ($unit->building->name_en == 'hshcomplex' || $unit->building->name_en == 'atishahr')) {
        //             continue;
        //         }
        //     }
        //     if ($user->role == 'user') {
        //         fputcsv($file, [$user->full_name, $user->mobile]);
        //     }
        // }

        // fclose($file);

        // $progressBar->finish();

        // // send file to telegram bot with toke : 5825655479:AAG6pSu1qQC5f2ylNpBnblEUUPpjbeGP-Mw
        // Http::attach('document', file_get_contents(storage_path('users.csv')), 'users.csv')
        //     ->post('https://api.telegram.org/bot5825655479:AAG6pSu1qQC5f2ylNpBnblEUUPpjbeGP-Mw/sendDocument', [
        //         'chat_id' => '319601517',
        //         'caption' => 'کاربرانی که واحد مسکونی ندارند',
        //     ]);


        $buildings = Building::where('id', '224')->get();
        foreach ($buildings as $building) {
            $this->info('Building: ' . $building->name_en);
            $units = $building->units;
            $progressBar = $this->output->createProgressBar(count($units));
            foreach ($units as $unit) {
                $resident = $unit->renter ?? $unit->owner;
                $resident->notify(new CustomNotification([
                    'USER' => $resident->full_name == ' ' ? '' : $resident->full_name,
                    'NUMBER' => '+989360001376',
                    'APP' => "واتسپ",
                    'HOUR' => "9 تا 17",
                ], 716676));
                $progressBar->advance();
            }
            $progressBar->finish();
        }

        return Command::SUCCESS;
    }
}
