<?php

namespace App\Http\Controllers\Api\V1\BuildingManager;

use App\Helpers\Inopay;
use App\Http\Controllers\Controller;
use App\Http\Resources\BuildingManager\BuildingOptionsResource;
use App\Http\Resources\BuildingManager\BuildingResource;
use App\Http\Resources\BuildingManager\BuildingUnitResource;
use App\Http\Resources\BuildingManager\ModuleResource;
use App\Http\Resources\BuildingManager\UserWithBusinessResource;
use App\Mail\ContractMail;
use App\Mail\CustomMail;
use App\Mail\NewBuildingMail;
use App\Models\Building;
use App\Models\BuildingUnit;
use App\Models\Poulpal\PoulpalBusiness;
use App\Notifications\ContractNotification;
use App\Notifications\OtpBackupNotification;
use App\Rules\NationalId;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Morilog\Jalali\Jalalian;

class ProfileController extends Controller
{

    public function __construct()
    {
        $this->middleware('restrictBuildingManager:other')->except(['stats', 'show', 'contract']);
        $this->middleware('restrictBuildingManager:hsh-1')->except(['stats', 'show', 'contract']);
    }

    public function stats(Request $request)
    {
        $building_manager = auth()->buildingManager();
        $building = $building_manager->building;
        $balance = $building->balance + $building->toll_balance;
        $balance = ($building->name_en == 'hshcomplex' && $building_manager->building_manager_type != 'superadmin') ? ($balance - 50000000) : $balance;
        $balance = ($building->name_en == 'atishahr' && $building_manager->building_manager_type != 'superadmin') ? ($balance - 40000000) : $balance;
        $balance = $balance < 0 ? 0 : $balance;
        $balance = ($building->name_en == 'hshcomplex' && $building_manager->building_manager_type != 'superadmin') ? 20626015 : $balance;
        $units_with_debt = $building->units()->where('charge_debt', '>', 0)->orderBy('charge_debt', 'desc')->get();
        $units_with_deposit = $building->units()->where('charge_debt', '<', 0)->orderBy('charge_debt', 'asc')->get();
        $units_with_zero_debt = $building->units()->where('charge_debt', 0)->get();

        // $start_date = Carbon::parse('2024-05-02 11:00');

        // if ($building->name_en == 'hshcomplex' || $building->name_en == 'atishahr') {
        //     $balance = $balance - $building->invoices()->where('status', 'paid')->where('is_verified', true)->where('amount', '>', 0)
        //         ->whereNot('payment_method', 'cash')
        //         ->where('created_at', '>', $start_date)
        //         ->sum('amount');
        // }

        if (auth()->buildingManager()->building->name_en == 'hshcomplex') {
            $start_date = Carbon::parse('2024-05-15 08:30');
            $ids = [];
            $ins = auth()->buildingManager()->building
                ->invoices()
                ->where('status', 'paid')
                ->where('is_verified', 1)
                ->where('serviceable_type', 'App\Models\BuildingUnit')
                ->whereNot('payment_method', 'cash')
                ->where('created_at', '>', $start_date)
                ->get();

            $sum = 0;
            foreach ($ins as $in) {
                if (floor($in->id / 2) % 2 == 0) {
                    $sum += $in->amount;
                    if ($sum <= 61000000) {
                        $ids[] = $in->id;
                    }
                }
            }

            $balance = $balance - $ins->whereIn('id', $ids)->sum('amount');
        }

        if (auth()->buildingManager()->building->name_en == 'hshcomplex') {
            // $start_date = Carbon::parse('2024-05-25 12:45');
            // $days = 3;
            // $today = Carbon::now()->endofDay();
            // if ($today->diff($start_date)->days >= $days) {
            //     $limit = $today->addDays(-1 * $days);
            // } else {
            //     $limit = $start_date;
            // }
            $exception_units = ['6063'];
            $ids = BuildingUnit::whereIn('unit_number', $exception_units)->pluck('id');
            $invoices = auth()->buildingManager()->building
                ->invoices()->where(function ($query) use ($ids) {
                    $query->whereNot('payment_method', 'cash')
                        ->whereNotIn('serviceable_id', $ids)
                        ->where('status', 'paid')
                        ->where('is_verified', 1)
                        // ->where('created_at', '>', $limit);
                        ->whereBetween('created_at', [Carbon::parse('2024-05-25 12:45'), Carbon::parse('2024-05-26 08:00')]);
                });

            $balance = $balance - $invoices->sum('amount');
        }

        if (auth()->buildingManager()->building->name_en == 'hshcomplex') {
            $exception_units = ['6063'];
            $ids = BuildingUnit::whereIn('unit_number', $exception_units)->pluck('id');
            $invoices = auth()->buildingManager()->building
                ->invoices()->where(function ($query) use ($ids) {
                    $query->whereNot('payment_method', 'cash')
                        ->whereNotIn('serviceable_id', $ids)
                        ->where('status', 'paid')
                        ->where('is_verified', 1)
                        // ->where('created_at', '>', Carbon::parse('2024-06-08 10:15'));
                        ->whereBetween('created_at', [Carbon::parse('2024-06-08 10:15'), Carbon::parse('2024-06-08 23:59')]);
                });

            $balance = $balance - $invoices->sum('amount');
        }

        if (auth()->buildingManager()->building->name_en == 'hshcomplex') {
            $exception_units = ['6063'];
            $ids = BuildingUnit::whereIn('unit_number', $exception_units)->pluck('id');
            $invoices = auth()->buildingManager()->building
                ->invoices()->where(function ($query) use ($ids) {
                    $query->whereNot('payment_method', 'cash')
                        ->whereNotIn('serviceable_id', $ids)
                        ->where('status', 'paid')
                        ->where('is_verified', 1)
                        // ->where('created_at', '>', Carbon::parse('2024-06-08 10:15'));
                        ->whereBetween('created_at', [Carbon::parse('2024-06-15 16:00'), Carbon::parse('2024-06-16 23:59')]);
                });

            $balance = $balance - $invoices->sum('amount');
        }

        if (auth()->buildingManager()->building->name_en == 'hshcomplex') {
            $exception_units = ['6063'];
            $ids = BuildingUnit::whereIn('unit_number', $exception_units)->pluck('id');
            $invoices = auth()->buildingManager()->building
                ->invoices()->where(function ($query) use ($ids) {
                    $query->whereNot('payment_method', 'cash')
                        ->whereNotIn('serviceable_id', $ids)
                        ->where('status', 'paid')
                        ->where('is_verified', 1)
                        // ->where('created_at', '>', Carbon::parse('2024-06-17 08:00'));
                        ->whereBetween('created_at', [Carbon::parse('2024-06-17 08:00'), Carbon::parse('2024-06-19 08:00')]);
                });

            $balance = $balance - $invoices->sum('amount');
        }

        $balance = $balance < 0 ? 0 : $balance;

        $payment_per_weekday = $building
            ->invoices()
            ->select(
                DB::raw('WEEKDAY(created_at) as day'),
                DB::raw('SUM(amount) as sum'),
                DB::raw('COUNT(*) as count'),
            )
            ->where('serviceable_type', BuildingUnit::class)
            ->whereNot('payment_method', 'cash')
            ->where('status', 'paid')
            ->where('is_verified', true)
            ->where('amount', '>', 0)
            ->groupBy('day')
            ->get()->toArray();

        foreach ($payment_per_weekday as $key => $item) {
            $payment_per_weekday[$key]['day'] = Jalalian::fromCarbon(Carbon::now()->startOfWeek()->addDays($item['day']))->format('l');
            $payment_per_weekday[$key]['dayNumber'] = Jalalian::fromCarbon(Carbon::now()->startOfWeek()->addDays($item['day']))->format('N');
        }
        $payment_per_weekday = collect($payment_per_weekday)->sortBy(function ($item, $key) {
            return $item['dayNumber'];
        })->values()->toArray();

        $payment_per_day = $building
            ->invoices()
            ->select(
                DB::raw("CAST(PDAY(created_at) AS UNSIGNED) as day"),
                DB::raw('SUM(amount) as sum'),
                DB::raw('COUNT(*) as count'),
            )
            ->where('serviceable_type', BuildingUnit::class)
            ->whereNot('payment_method', 'cash')
            ->where('status', 'paid')
            ->where('is_verified', true)
            ->where('amount', '>', 0)
            ->groupBy('day')->get()->toArray();

        $contract_url = route('v1.public.profile.contract', ['key' => $building->contract_key]);

        $balances = $building->balances ?? [];

        if ($building->units()->count() < $building->unit_count) {
            // $banner_text = __("شما هنوز  ") . ($building->unit_count - $building->units()->count()) . __(" واحد از مجموعه ") . $building->unit_count . __(" واحدی خود را تعریف نکرده اید");
            $banner_text = __("شما هنوز :count واحد از مجموعه :total واحدی خود را تعریف نکرده اید", ['count' => $building->unit_count - $building->units()->count(), 'total' => $building->unit_count]);
            $banner_cta = '/buildingManager/units';
            $banner_cta_text = __("افزودن واحد");
        }

        if ($building->units()->count() == 0) {
            $banner_text = __("شما هنوز واحدی برای مجموعه خود تعریف نکرده اید، برای افزودن واحدها اینجا را کلیک کنید");
            $banner_cta = '/buildingManager/units';
            $banner_cta_text = __("افزودن واحد");
        }

        if ($building->units()->count() >= $building->unit_count && $building->invoices()->where('amount', '<', 0)->count() == 0) {
            $banner_text = __("بدهی قبلی برخی از واحدهای تعریف شده مشخص نیست و در سیستم  فاقد بدهی تعریف شده اند؛ برای تعریف سابقه بدهی واحدهای جدید؛ اینجا را کلیک کنید");
            $banner_cta = '/buildingManager/debts';
            $banner_cta_text = __("بدهی ها");
        }

        // if ($building->created_at->diffInDays(Carbon::now()) <= 7 && app()->getLocale() == 'fa') {
        //     $popup_title = "فرصتی طلایی در دستان شما!";
        //     $popup_text = "فرصتی طلایی در دستان شما!

        //     به مدت ۷ روز، نرم‌افزار خدمات ساختمان و مدیریت ما را به صورت رایگان تجربه کنید. اما این همه ماجرا نیست!

        //     تخفیف استثنایی منتظر شماست:

        //         روز اول: ۱۵% تخفیف
        //         روز دوم: ۱۴% تخفیف
        //         روز سوم: ۱۳% تخفیف
        //         روز چهارم: ۱۲% تخفیف
        //         روز پنجم: ۱۱% تخفیف
        //         روز ششم: ۱۰% تخفیف
        //         روز هفتم: آخرین فرصت! ۰% تخفیف


        //     هر روز که می‌گذرد، تخفیف کمتر می‌شود. پس این فرصت را از دست ندهید!



        //     🔥 همین حالا ثبت نام کنید و با بهترین تخفیف، به دنیای مدیریت هوشمند ساختمان‌ها قدم بگذارید.


        //     شارژپل  حرفه ای و برای بهترین ها!";

        //     $popup_cta = '/buildingManager/modules';
        //     $popup_cta_text = __("ثبت نام");
        // }

        if (config('app.type') === 'kaino') {
            $inopay = new Inopay();
            $balance = $inopay->getBalance($building);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => $balance,
                'balances' => $balances,
                'units_with_debt' => BuildingUnitResource::collection($units_with_debt),
                'units_with_debt_sum' => round($units_with_debt->sum('charge_debt'), 1),
                'units_with_deposit' => BuildingUnitResource::collection($units_with_deposit),
                'units_with_deposit_sum' => round($units_with_deposit->sum('charge_debt'), 1),
                'units_with_zero_debt' => BuildingUnitResource::collection($units_with_zero_debt),
                'payment_per_weekday' => $payment_per_weekday,
                'payment_per_day' => $payment_per_day,
                'signed_contract' => $building_manager->building_manager_type == 'main' && app()->getLocale() == 'fa' ? (bool)$building->signed_contract : true,
                'contract_url' => $contract_url,
                'contract_mobile' => $building_manager->building_manager_type == 'main' ? $building->mainBuildingManagers()->first()->mobile : null,
                'banner' => [
                    'text' => $banner_text ?? null,
                    'cta' => $banner_cta ?? null,
                    'cta_text' => $banner_cta_text ?? null,
                ],
                'popup' => [
                    'title' => $popup_title ?? null,
                    'text' => $popup_text ?? null,
                    'cta' => $popup_cta ?? null,
                    'cta_text' => $popup_cta_text ?? null,
                ],
            ]
        ]);
    }

    public function contract(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string',
        ]);

        if ($validator->fails()) {
            return abort(442);
        }

        $building = Building::where('contract_key', $request->key)->firstOrFail();
        $building_manager = $building->mainBuildingManagers()->first();
        return view('pdf.contract', compact('building_manager', 'building'));
    }

    public function sendContractOtp()
    {
        if (auth()->buildingManager()->otp != null  && auth()->buildingManager()->otp_expires_at > Carbon::now()) {
            // $wait = Carbon::now()->diffForHumans(auth()->buildingManager()->otp_expires_at);
            $wait = Carbon::parse(auth()->buildingManager()->otp_expires_at)->diffInSeconds(Carbon::now());
            return response()->json([
                'success' => false,
                'message' => __("لطفا ") . $wait . __(" ثانیه دیگر درخواست کنید"),
            ], 422);
        }

        if (auth()->buildingManager()->building->signed_contract) {
            return response()->json([
                'success' => false,
                'message' => __("قرارداد قبلا امضا شده است"),
            ], 422);
        }

        $generated_otp = random_int(100000, 999999);
        $building_manager = auth()->buildingManager()->building->mainBuildingManagers()->first();
        $ttl = 3;
        $building_manager->update([
            'otp' => $generated_otp,
            'otp_expires_at' => Carbon::now()->addMinutes($ttl),
        ]);
        Cache::forget('building_manager_' . $building_manager->id);
        $this->notifyUser($building_manager, $generated_otp);
        return response()->json([
            'success' => true,
            'message' => __("کد ارسال شد"),
            'data' => [
                'otp' => config('app.env') !== 'production' ? $generated_otp : null,
                'otp_expires_at' => Carbon::now()->addMinutes($ttl),
            ]
        ]);
    }

    public function verifyContractOtp()
    {
        if (auth()->buildingManager()->otp_expires_at < Carbon::now()) {
            return response()->json([
                'success' => false,
                'message' => __("لطفا درخواست رمز جدیدی ارسال کنید"),
            ], 422);
        }

        if (auth()->buildingManager()->building->signed_contract) {
            return response()->json([
                'success' => false,
                'message' => __("قرارداد قبلا امضا شده است"),
            ], 422);
        }

        if (auth()->buildingManager()->otp != request()->code) {
            return response()->json([
                'success' => false,
                'message' => __("کد وارد شده صحیح نمی باشد"),
            ], 422);
        }

        if (auth()->buildingManager()->otp == request()->code) {
            auth()->buildingManager()->update([
                'otp' => null,
            ]);
            auth()->buildingManager()->building()->update([
                'signed_contract' => true,
            ]);

            if (auth()->buildingManager()->building->mainBuildingManagers->first()->details->email) {
                Mail::to([auth()->buildingManager()->building->mainBuildingManagers->first()->details->email])->send(
                    new ContractMail(
                        auth()->buildingManager()->building,
                        auth()->buildingManager()->building->mainBuildingManagers->first(),
                    )
                );
            }

            Mail::to(['sales@cc2com.com', 'arcenciel.ir@gmail.com', 'saman.moayeri@gmail.com'])->send(
                new ContractMail(
                    auth()->buildingManager()->building,
                    auth()->buildingManager()->building->mainBuildingManagers->first(),
                )
            );
            Cache::forget('building_manager_' . auth()->buildingManager()->id);

            return response()->json([
                'success' => true,
                'message' => __("قرارداد با موفقیت امضا شد"),
            ]);
        }
    }


    public function show(Request $request)
    {
        $building_manager = auth()->buildingManager()->building->mainBuildingManagers()->first();
        $activeModules = auth()->buildingManager()->building->modules;
        $options = $building_manager->building->options;
        $options->excel_export = true;
        if ($building_manager->building->name_en == 'hshcomplex' && $building_manager->building_manager_type == 'other') {
            $options->excel_export = false;
        }
        return response()->json([
            'success' => true,
            'data' => [
                'building_manager' => new UserWithBusinessResource(auth()->buildingManager()),
                'building' => new BuildingResource($building_manager->building),
                'has_credit' => $building_manager->building->id == 2 ? true : false,
                'options' => BuildingOptionsResource::make($options),
                'activeModules' => ModuleResource::collection($activeModules),
            ]
        ]);
    }

    public function updateBuildingImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $building_manager = auth()->buildingManager();
        $image = $request->image->store('public/building_images');
        $image_url = Storage::url($image);
        $building_manager->building()->update([
            'image' => $image_url,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'image' => asset($image_url),
            ]
        ]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required',
            'national_id' => ['required', 'numeric', new NationalId, 'unique:details,national_id'],
            'province' => 'required',
            'district' => 'required|numeric',
            'postal_code' => 'required|regex:/[0-9]{10}/',
            'sheba_number' => 'required|regex:/[0-9]{24}/',
            'card_number' => 'required|regex:/[0-9]{16}/',
            'national_card_image' => 'nullable|mimes:png,jpg,jpeg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $building_manager = auth()->buildingManager()->building->mainBuildingManagers()->first();

        $building_manager->details()->update([
            'phone_number' => $request->phone_number,
            'national_id' => $request->national_id,
            'province' => $request->province,
            'district' => $request->district,
            'postal_code' => $request->postal_code,
            'sheba_number' => $request->sheba_number,
            'card_number' => $request->card_number,
        ]);

        if ($request->hasFile('national_card_image')) {
            Mail::to(['cc2com.com@gmail.com', 'arcenciel.ir@gmail.com', 'saman.moayeri@gmail.com', 'poulpal33@poulpal.com'])->send(new NewBuildingMail(
                'آپدیت اطلاعات ساختمان - ' . $building_manager->building->name,
                "
                اطلاعات ساختمان آپدیت شد. <br>
                نام ساختمان: " . $building_manager->building->name . " - " . $building_manager->building->name_en . " <br>
                نام مدیر: $building_manager->first_name $building_manager->last_name <br>
                شماره موبایل: $building_manager->mobile <br>
                آدرس: " . $building_manager->details->address . " <br>
                کد پستی: " . $building_manager->details->postal_code . " <br>
                ایمیل: " . $building_manager->details->email . " <br>
                شماره شبا: " . $building_manager->details->sheba_number . " <br>
                شماره کارت: " . $building_manager->details->card_number . " <br>
                تاریخ آپدیت: " . Jalalian::now()->format('Y-m-d H:i:s') . "<br>",
                $request->national_card_image ?? null,
            ));

            $building_manager->details()->update([
                'national_card_image' => 'ok',
            ]);
        }

        // $this->handleUpdatePoulpalBusiness($building_manager);

        return response()->json([
            'success' => true,
            'message' => __("اطلاعات با موفقیت ثبت شد"),
            'data' => [
                'building_manager' => new UserWithBusinessResource($building_manager),
                'building' => new BuildingResource($building_manager->building),
            ]
        ]);
    }


    private function handleUpdateBuilding($building_manager)
    {
        return;
        try {
            $building = $building_manager->building;

            $poulpal_business = PoulpalBusiness::where('id', $building->poulpal_business_id)->first();
            $poulpal_business->national_id = $building->details->national_id;
            $poulpal_business->postal_code = $building->details->postal_code;
            $poulpal_business->email = $building->details->email;
            $poulpal_business->sheba_number = $building->details->sheba_number;
            $poulpal_business->card_number = $building->details->card_number;
            $poulpal_business->national_card_image = $building->details->national_card_image;

            $poulpal_business->save();
        } catch (\Exception $e) {
            Mail::to('cc2com.com@gmail.com')->send(new CustomMail(__("خطا در ویرایش اطلاعات ساختمان "), $e->getMessage()));
            Log::error($e->getMessage());
        }
    }

    private function notifyUser($user, $otp)
    {
        if (config('app.env') !== 'production') {
            return;
        }
        try {
            $user->notify(new ContractNotification($otp));
        } catch (\Throwable $th) {
            $user->notify(new OtpBackupNotification($otp));
        }
    }
}
