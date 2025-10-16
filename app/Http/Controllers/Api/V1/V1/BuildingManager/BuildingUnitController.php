<?php

namespace App\Http\Controllers\Api\V1\BuildingManager;

use App\Http\Controllers\Controller;
use App\Http\Resources\BuildingManager\BuildingUnitWithResidentsResource;
use App\Mail\CustomMail;
use App\Models\BuildingUnit;
use App\Models\Invoice;
use App\Models\User;
use App\Notifications\User\CustomNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Intervention\Image\Facades\Image;
use Morilog\Jalali\Jalalian;

class BuildingUnitController extends Controller
{

    public function __construct()
    {
        // add middleware to controller
        $this->middleware('verifyBusiness')->only(['store', 'qrcodes']);
        $this->middleware('restrictBuildingManager:other')->except(['index', 'qrcodes', 'show']);
        $this->middleware('restrictBuildingManager:hsh-1')->except(['index', 'qrcodes', 'show', 'addInvoice', 'addMultipleDebt', 'addMultipleDeposit']);
        $this->middleware('hasModule:base')->except(['index', 'show']);
    }

    public function index()
    {
        $units = auth()->buildingManager()->building->units();
        if (request()->has('onlyFcm') && request()->onlyFcm) {
            $units = $units->whereHas('residents', function ($query) {
                $query->has('fcm_tokens');
            });
        }
        if (request()->has('withResidents') && request()->withResidents) {
            $units = $units->with('residents');
        }
        $units  = $units->orderByRaw('CONVERT(unit_number, SIGNED)')->get();
        return response()->json([
            'success' => true,
            'data' => [
                'units' => BuildingUnitWithResidentsResource::collection($units),
            ]
        ], 200);
    }

    public function qrcodes()
    {
        $building = auth()->buildingManager()->building;
        $building_name_en = $building->name_en;
        $zip = new \ZipArchive();
        $zip->open(public_path('img/qrcodes/' . $building_name_en . '.zip'), \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        foreach ($building->units as $unit) {
            $image_name = $building_name_en . "_" . $unit->unit_number . ".png";
            if (file_exists(public_path("img/qrcodes/" . $image_name))) {
                $zip->addFile(public_path("img/qrcodes/" . $image_name), $image_name);
                continue;
            }
            $qrcode = Image::make(base64_encode(QrCode::format('png')->errorCorrection('H')->size(310)->margin(1)->backgroundColor(255, 255, 255)->color(0, 0, 0)->generate("https://c36.ir/b" . $unit->token)));
            $output = Image::make(public_path("img/qrcode_template.png"));
            $output->insert($qrcode, 'center', $x = 0, $y = -12);
            $Arabic = new \ArPHP\I18N\Arabic();
            $text = $Arabic->utf8Glyphs($unit->unit_number);
            $output->text($unit->unit_number, 525, 805, function ($font) {
                $font->file(public_path('fnt/Samim-Bold-FD.ttf'));
                $font->size(50);
                $font->color('#f58220');
                $font->align('center');
                $font->valign('center');
            });
            $output->save(public_path("img/qrcodes/" . $image_name));
            $zip->addFile(public_path("img/qrcodes/" . $image_name), $image_name);
        }
        $zip->close();
        return response()->download(public_path('img/qrcodes/' . $building_name_en . '.zip'));
    }

    public function show($id)
    {
        $unit = auth()->buildingManager()->building->units()->where('id', $id)->first();
        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found',
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => [
                'unit' => new BuildingUnitWithResidentsResource($unit),
            ]
        ]);
    }

    public function store(Request $request)
    {
        try {
            if (auth()->buildingManager()->building->units()->count() >= auth()->buildingManager()->building->modules()->where('type', 'base')->first()->features->limit) {
                return response()->json([
                    'success' => false,
                    'message' => 'تعداد واحدهای ساختمان شما به حداکثر رسیده است . برای افزودن واحد جدید نیاز به خرید پکیج پایه دارید',
                ], 422);
            }
        } catch (\Throwable $th) {
            if (auth()->buildingManager()->building->units()->count() >= 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'تعداد واحدهای ساختمان شما به حداکثر رسیده است . برای افزودن واحد جدید نیاز به خرید پکیج پایه دارید',
                    'action' => 'buy_base_module'
                ], 422);
            }
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'nullable',
            'last_name' => 'nullable',
            'mobile' => app()->getLocale() == 'en'
                ? 'required|regex:/^(\+1)?\s*\(?[2-9]\d{2}\)?[-.\s]?\d{3}[-.\s]?\d{4}$/'
                : 'required|digits:11',
            'unit_number' => 'required',
            'charge_fee' => 'required|decimal:0,1|min:0',
            'rent_fee' => 'nullable|decimal:0,1|min:0',
            'ownership' => 'required|in:owner,renter',
            'area' => 'nullable|decimal:0,2|min:0',
            'resident_count' => 'nullable|integer|min:0',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $user = User::where('mobile', $request->mobile)->first();

        if (!$user) {
            $user = new User();
            if ($request->first_name) {
                $user->first_name = $request->first_name;
            }
            if ($request->last_name) {
                $user->last_name = $request->last_name;
            }
            $user->mobile = $request->mobile;
            $user->save();
        } else {
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->save();
        }

        if ($user->building_units()->where('building_id', auth()->buildingManager()->building->id)->where('unit_number', $request->unit_number)->first()) {
            return response()->json([
                'success' => false,
                'message' => __("ساکن قبلا به این واحد اضافه شده است"),
            ], 422);
        }

        $trashed_unit = BuildingUnit::onlyTrashed()->where('building_id', auth()->buildingManager()->building->id)->where('unit_number', $request->unit_number)->first();

        if ($trashed_unit) {
            $trashed_unit->restore();
            $trashed_unit->charge_fee = $request->charge_fee;
            $trashed_unit->rent_fee = $request->rent_fee ?? 0;
            $trashed_unit->area = $request->area;
            $trashed_unit->resident_count = $request->resident_count;
            $trashed_unit->save();
            $this->detachResidents($trashed_unit, $trashed_unit->residents);
            $trashed_unit->residents()->attach($user->id, ['ownership' => $request->ownership]);
            return response()->json([
                'success' => true,
                'message' => __("ساکن با موفقیت اضافه شد"),
                'data' => [
                    'unit' => new BuildingUnitWithResidentsResource($trashed_unit),
                ]
            ], 201);
        }

        $building_unit = BuildingUnit::where('building_id', auth()->buildingManager()->building->id)->where('unit_number', $request->unit_number)->first();
        if (!$building_unit) {
            $building_unit = new BuildingUnit();
            $building_unit->building_id = auth()->buildingManager()->building->id;
            $building_unit->unit_number = $request->unit_number;
            $building_unit->charge_fee = $request->charge_fee;
            $building_unit->rent_fee = $request->rent_fee ?? 0;
            $building_unit->area = $request->area;
            $building_unit->resident_count = $request->resident_count;
            $building_unit->save();
        } else {
            return response()->json([
                'success' => false,
                'message' => __("واحد قبلا اضافه شده است"),
            ], 400);
        }

        $building_unit->residents()->attach($user->id, ['ownership' => $request->ownership]);

        Mail::to(['arcenciel.ir@gmail.com', 'saman.moayeri@gmail.com', 'poulpal33@poulpal.com'])->send(new CustomMail(
            'واحد جدید ثبت شد - ' . $building_unit->building->name,
            "
            واحد جدید ثبت شده است. <br>
            شماره واحد: $building_unit->unit_number <br>
            نام مالک: $user->first_name $user->last_name <br>
            شماره موبایل: $user->mobile <br>
            مساحت: $building_unit->area متر مربع <br>
            تعداد ساکنین: $building_unit->resident_count <br>
            شارژ ماهیانه: $building_unit->charge_fee تومان <br>
            تاریخ ثبت نام: " . Jalalian::now()->format('Y-m-d H:i:s') . "<br>"
        ));

        $user->notify(new CustomNotification([
            'USER' => $user->full_name == __(" ") ? __("کاربر") : $user->full_name,
            'SECTION' => __("شارژپل"),
            'MANAGER' => auth()->buildingManager()->full_name,
        ], 878597));

        return response()->json([
            'success' => true,
            'message' => __("ساکن با موفقیت اضافه شد"),
            'data' => [
                'unit' => new BuildingUnitWithResidentsResource($building_unit),
            ]
        ], 201);
    }

    public function addMultipleUnits(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'units' => 'required|array',
            'units.*.unit_number' => 'required',
            'units.*.charge_fee' => 'required|decimal:0,1',
            'units.*.first_name' => 'nullable',
            'units.*.last_name' => 'nullable',
            'units.*.mobile' => 'required|digits:11',
            'units.*.ownership' => 'required|in:owner,renter',
            'units.*.area' => 'nullable|decimal:0,2|min:0',
            'units.*.resident_count' => 'nullable|integer|min:0',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        if (auth()->buildingManager()->building->units()->count() >= auth()->buildingManager()->building->modules()->where('type', 'base')->first()->features->limit) {
            return response()->json([
                'success' => false,
                'message' => __("تعداد واحدهای ساختمان شما به حداکثر رسیده است"),
            ], 422);
        }elseif (auth()->buildingManager()->building->units()->count() + count($request->units) > auth()->buildingManager()->building->modules()->where('type', 'base')->first()->features->limit) {
            return response()->json([
                'success' => false,
                'message' => __("شما قادر به اضافه کردن ") . (auth()->buildingManager()->building->modules()->where('type', 'base')->first()->features->limit - auth()->buildingManager()->building->units()->count()) . __(" واحد دیگر هستید"),
            ], 422);
        }

        $building_manager = auth()->buildingManager();
        $units = $request->units;

        DB::connection('mysql')->beginTransaction();
        try {
            foreach ($units as $unit) {
                $user = User::where('mobile', $unit['mobile'])->first();

                if (!$user) {
                    $user = new User();
                    if (isset($unit['first_name'])) {
                        $user->first_name = $unit['first_name'];
                    }
                    if (isset($unit['last_name'])) {
                        $user->last_name = $unit['last_name'];
                    }
                    $user->mobile = $unit['mobile'];
                    $user->save();
                } else {
                    if (isset($unit['first_name'])) {
                        $user->first_name = $unit['first_name'];
                    }
                    if (isset($unit['last_name'])) {
                        $user->last_name = $unit['last_name'];
                    }
                    $user->save();
                }

                if ($user->building_units()->where('building_id', auth()->buildingManager()->building->id)->where('unit_number', $unit['unit_number'])->first()) {
                    throw new \Exception(__("واحد با شماره ") . $unit['unit_number'] . __(" قبلا به این ساختمان اضافه شده است"));
                }

                $trashed_unit = BuildingUnit::onlyTrashed()->where('building_id', auth()->buildingManager()->building->id)->where('unit_number', $unit['unit_number'])->first();

                if ($trashed_unit) {
                    $trashed_unit->restore();
                    $trashed_unit->charge_fee = $unit['charge_fee'];
                    $trashed_unit->area = $unit['area'];
                    $trashed_unit->resident_count = $unit['resident_count'];
                    $trashed_unit->save();
                    if ($trashed_unit->owner && $unit['ownership'] == 'owner') {
                        throw new \Exception(__("مالک قبلا به واحد ") . $unit['unit_number'] . __(" اضافه شده است"));
                    }
                    if ($trashed_unit->renter && $unit['ownership'] == 'renter') {
                        throw new \Exception(__("مستاجر قبلا به واحد ") . $unit['unit_number'] . __(" اضافه شده است"));
                    }
                    $this->detachResidents($trashed_unit, $trashed_unit->residents);
                    $trashed_unit->residents()->attach($user->id, ['ownership' => $unit['ownership']]);
                    continue;
                }

                $building_unit = BuildingUnit::where('building_id', auth()->buildingManager()->building->id)->where('unit_number', $unit['unit_number'])->first();
                if (!$building_unit) {
                    $building_unit = new BuildingUnit();
                    $building_unit->building_id = auth()->buildingManager()->building->id;
                    $building_unit->unit_number = $unit['unit_number'];
                    $building_unit->charge_fee = $unit['charge_fee'];
                    $building_unit->area = $unit['area'];
                    $building_unit->resident_count = $unit['resident_count'];
                    $building_unit->save();
                }

                if ($building_unit->owner && $unit['ownership'] == 'owner') {
                    throw new \Exception(__("مالک قبلا به واحد ") . $unit['unit_number'] . __(" اضافه شده است "));
                }

                if ($building_unit->renter && $unit['ownership'] == 'renter') {
                    throw new \Exception(__("مستاجر قبلا به واحد ") . $unit['unit_number'] . __(" اضافه شده است "));
                }

                $building_unit->residents()->attach($user->id, ['ownership' => $unit['ownership']]);
            }
            DB::connection('mysql')->commit();
        } catch (\Exception $e) {
            DB::connection('mysql')->rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }

        // ارسال Notification بعد از commit موفق
        foreach ($units as $unit) {
            $user = User::where('mobile', $unit['mobile'])->first();
            $user->notify(new CustomNotification([
                'USER' => $user->full_name == __(" ") ? __("کاربر") : $user->full_name,
                'SECTION' => __("شارژپل"),
                'MANAGER' => auth()->buildingManager()->full_name,
            ], 878597));
        }

        Mail::to(['arcenciel.ir@gmail.com', 'saman.moayeri@gmail.com', 'poulpal33@poulpal.com'])->send(new CustomMail(
            'واحدهای جدید ثبت شدند - ' . auth()->buildingManager()->building->name,
            "
            واحدهای جدید ثبت شده اند. <br>
            تعداد واحدها: " . count($units) . "<br>
            تاریخ ثبت نام: " . Jalalian::now()->format('Y-m-d H:i:s') . "<br>"
        ));

        return response()->json([
            'success' => true,
            'message' => __("واحدها با موفقیت اضافه شدند"),
        ], 201);
    }

    public function addResident($unit, Request $request)
    {
        $request->merge(['withResidents' => true]);
        $unit = auth()->buildingManager()->building->units()->where('id', $unit)->firstOrFail();
        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => "Not found"
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'nullable',
            'last_name' => 'nullable',
            'mobile' => app()->getLocale() == 'en'
                ? 'required|regex:/^(\+1)?\s*\(?[2-9]\d{2}\)?[-.\s]?\d{3}[-.\s]?\d{4}$/'
                : 'required|digits:11',
            'ownership' => 'required|in:owner,renter',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $user = User::where('mobile', $request->mobile)->first();

        if (!$user) {
            $user = new User();
            if ($request->first_name) {
                $user->first_name = $request->first_name;
            }
            if ($request->last_name) {
                $user->last_name = $request->last_name;
            }
            $user->mobile = $request->mobile;
            $user->save();
        } else {
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->save();
        }

        if ($user->building_units()->where('building_id', auth()->buildingManager()->building->id)->where('unit_number', $unit->unit_number)->first()) {
            return response()->json([
                'success' => false,
                'message' => __("ساکن قبلا به این واحد اضافه شده است"),
            ], 422);
        }

        if ($unit->owner && $request->ownership == 'owner') {
            return response()->json([
                'success' => false,
                'message' => __("مالک قبلا به این واحد اضافه شده است"),
            ], 422);
        }

        if ($unit->renter && $request->ownership == 'renter') {
            return response()->json([
                'success' => false,
                'message' => __("مستاجر قبلا به این واحد اضافه شده است"),
            ], 422);
        }

        $unit->residents()->attach($user->id, ['ownership' => $request->ownership]);

        return response()->json([
            'success' => true,
            'message' => __("ساکن با موفقیت اضافه شد"),
            'data' => [
                'unit' => new BuildingUnitWithResidentsResource($unit),
            ]
        ], 201);
    }

    public function removeResident($unit, $resident)
    {
        $unit = auth()->buildingManager()->building->units()->where('id', $unit)->firstOrFail();
        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => "Not found"
            ], 404);
        }
        $this->detachResidents($unit, User::where('id', $resident)->get());
        return response()->json([
            'success' => true,
            'message' => __("ساکن با موفقیت حذف شد"),
            'data' => [
                'unit' => new BuildingUnitWithResidentsResource($unit),
            ]
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'charge_fee' => 'required|decimal:0,1|min:0',
            'rent_fee' => 'nullable|decimal:0,1|min:0',
            'area' => 'nullable|decimal:0,2|min:0',
            'resident_count' => 'nullable|integer|min:0',
            'unit_number' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $unit = auth()->buildingManager()->building->units()->where('id', $id)->first();
        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => "Not found"
            ], 404);
        }

        $unit->charge_fee = $request->charge_fee;
        $unit->rent_fee = $request->rent_fee ?? 0;
        $unit->unit_number = $request->unit_number;
        $unit->area = $request->area ?? $unit->area;
        $unit->resident_count = $request->resident_count ?? $unit->resident_count;

        $unit->save();

        return response()->json([
            'success' => true,
            'message' => __("واحد با موفقیت ویرایش شد"),
            'data' => [
                'unit' => new BuildingUnitWithResidentsResource($unit),
            ]
        ], 200);
    }

    public function addInvoice(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:deposit,debt',
            'amount' => ['required', 'decimal:0,1', function ($attribute, $value, $fail) use ($request) {
                if ($value < 0) {
                    $fail(__("مبلغ وارد شده باید بزرگتر از صفر باشد"));
                }
            }],
            'description' => 'required',
            'date' => 'required|date',
            'resident_type' => 'nullable|in:owner,resident',
            'debt_type_id' => 'nullable|exists:debt_types,id,building_id,' . auth()->buildingManager()->building->id,
            'bank_id' => 'nullable|exists:accounting_details,id,building_id,' . auth()->buildingManager()->building->id,
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $unit = auth()->buildingManager()->building->units()->where('id', $id)->first();
        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => "Not found"
            ], 404);
        }
        $unit->invoices()->create([
            'building_id' => $unit->building->id,
            'amount' => $request->type == 'deposit' ? $request->amount : -$request->amount,
            'status' => 'paid',
            'payment_method' => 'cash',
            'serviceable_type' => 'App\Models\BuildingUnit',
            'serviceable_id' => $unit->id,
            'description' => $request->description,
            'created_at' => Carbon::parse($request->date),
            'resident_type' => $request->resident_type ?? 'resident',
            'debt_type_id' => $request->type == 'debt' ? $request->debt_type_id : null,
            'bank_id' => $request->type == 'debt' ? null : $request->bank_id ?? auth()->buildingManager()->building->accountingDetails()->where('type', 'bank')->first()->id,
        ]);

        $unit->charge_debt = round($unit->charge_debt + ($request->type == 'debt' ? $request->amount : -$request->amount), 1);
        $unit->save();

        return response()->json([
            'success' => true,
            'message' => __("فاکتور با موفقیت اضافه شد"),
        ], 201);
    }

    public function addMultipleDebt(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:debt',
            'debt_type_id' => 'nullable|exists:debt_types,id,building_id,' . auth()->buildingManager()->building->id,
            'debts' => 'required|array',
            'debts.*.unit_number' => 'required',
            'debts.*.amount' => function ($attribute, $value, $fail) {
                [$group, $position, $name] = explode('.', $attribute);
                $position += 1;
                if (!Validator::make(['item' => $value], ['item' => 'required'])->passes()) {
                    $fail("لطفا مبلغ مورد $position را وارد کنید");
                }

                if (!Validator::make(['item' => $value], ['item' => 'decimal:0,1'])->passes()) {
                    $fail("لطفا مبلغ مورد $position را به درستی وارد کنید");
                }

                if ($value < 1) {
                    $fail("لطفا مبلغ مورد $position را به درستی وارد کنید");
                }
            },
            'debts.*.description' => 'required',
            'debts.*.date' => 'nullable',
            'debts.*.resident_type' => 'nullable|in:owner,resident',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $date_format = 'Y/m/d';

        DB::connection('mysql')->beginTransaction();

        try {
            foreach ($request->debts as $debt) {
                $unit = auth()->buildingManager()->building->units()->where('unit_number', $debt['unit_number'])->first();
                if (!$unit) {
                    throw new \Exception(__("واحد ") . $debt['unit_number'] . __(" در ساختمان شما موجود نیست"));
                }
                if(isset($debt['resident_type']) && $debt['resident_type'] == 'owner' && !$unit->owner){
                    throw new \Exception(__("مالک واحد ") . $debt['unit_number'] . __(" موجود نیست"));
                }
                Invoice::create([
                    'building_id' => $unit->building->id,
                    'amount' => -$debt['amount'],
                    'status' => 'paid',
                    'payment_method' => 'cash',
                    'serviceable_type' => 'App\Models\BuildingUnit',
                    'serviceable_id' => $unit->id,
                    'resident_type' => $debt['resident_type'] ?? 'resident',
                    'debt_type_id' => $request->debt_type_id ?? auth()->buildingManager()->building->debtTypes()->first()->id,
                    'description' => $debt['description'],
                    'created_at' => isset($debt['date']) ? Jalalian::fromFormat($date_format, $debt['date'])->toCarbon() : Carbon::now(),
                    'updated_at' => isset($debt['date']) ? Jalalian::fromFormat($date_format, $debt['date'])->toCarbon() : Carbon::now(),
                ]);

                $unit->charge_debt = round($unit->charge_debt + $debt['amount'], 1);
                $unit->save();
            }
            DB::connection('mysql')->commit();
        } catch (\Exception $e) {
            DB::connection('mysql')->rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'بدهی های مورد نظر با موفقیت افزوده شد.',
        ], 200);
    }

    public function addMultipleDeposit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:deposit',
            'bank_id' => 'nullable|exists:accounting_details,id,building_id,' . auth()->buildingManager()->building->id,
            'deposits' => 'required|array',
            'deposits.*.unit_number' => 'required',
            'deposits.*.amount' => function ($attribute, $value, $fail) {
                [$group, $position, $name] = explode('.', $attribute);
                $position += 1;
                if (!Validator::make(['item' => $value], ['item' => 'required'])->passes()) {
                    $fail("لطفا مبلغ مورد $position را وارد کنید");
                }

                if (!Validator::make(['item' => $value], ['item' => 'decimal:0,1'])->passes()) {
                    $fail("لطفا مبلغ مورد $position را به درستی وارد کنید");
                }

                if ($value < 1) {
                    $fail("لطفا مبلغ مورد $position را به درستی وارد کنید");
                }
            },
            'deposits.*.description' => 'required',
            'deposits.*.date' => 'nullable',
            'deposits.*.resident_type' => 'nullable|in:owner,resident',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $date_format = 'Y/m/d';

        DB::connection('mysql')->beginTransaction();

        try {
            foreach ($request->deposits as $deposit) {
                $unit = auth()->buildingManager()->building->units()->where('unit_number', $deposit['unit_number'])->first();
                if (!$unit) {
                    throw new \Exception(__("واحد ") . $deposit['unit_number'] . __(" در ساختمان شما موجود نیست"));
                }
                if(isset($deposit['resident_type']) && $deposit['resident_type'] == 'owner' && !$unit->owner){
                    throw new \Exception(__("مالک واحد ") . $deposit['unit_number'] . __(" موجود نیست"));
                }
                Invoice::create([
                    'building_id' => $unit->building->id,
                    'amount' => $deposit['amount'],
                    'status' => 'paid',
                    'payment_method' => 'cash',
                    'serviceable_type' => 'App\Models\BuildingUnit',
                    'serviceable_id' => $unit->id,
                    'resident_type' => $deposit['resident_type'] ?? 'resident',
                    'description' => $deposit['description'],
                    'created_at' => isset($deposit['date']) ? Jalalian::fromFormat($date_format, $deposit['date'])->toCarbon() : Carbon::now(),
                    'updated_at' => isset($deposit['date']) ? Jalalian::fromFormat($date_format, $deposit['date'])->toCarbon() : Carbon::now(),
                    'bank_id' => $request->bank_id ?? auth()->buildingManager()->building->accountingDetails()->where('type', 'bank')->first()->id,
                ]);

                $unit->charge_debt = round($unit->charge_debt - $deposit['amount'], 1);
                $unit->save();
            }
            DB::connection('mysql')->commit();
        } catch (\Exception $e) {
            DB::connection('mysql')->rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'پرداختی های مورد نظر با موفقیت افزوده شد.',
        ], 200);
    }

    public function setMultipleChargeFee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'charges' => 'required|array',
            'charges.*.unit_number' => 'required',
            'charges.*.amount' => function ($attribute, $value, $fail) {
                [$group, $position, $name] = explode('.', $attribute);
                $position += 1;
                if (!Validator::make(['item' => $value], ['item' => 'required'])->passes()) {
                    $fail("لطفا مبلغ مورد $position را وارد کنید");
                }

                if (!Validator::make(['item' => $value], ['item' => 'decimal:0,1'])->passes()) {
                    $fail("لطفا مبلغ مورد $position را به درستی وارد کنید");
                }

                if ($value < 0) {
                    $fail("لطفا مبلغ مورد $position را به درستی وارد کنید");
                }
            },
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::connection('mysql')->beginTransaction();

        try {
            foreach ($request->charges as $charge) {
                $unit = auth()->buildingManager()->building->units()->where('unit_number', $charge['unit_number'])->first();
                if (!$unit) {
                    throw new \Exception(__("واحد ") . $charge['unit_number'] . __(" در ساختمان شما موجود نیست"));
                }
                $unit->charge_fee = $charge['amount'];
                $unit->save();
            }
            DB::connection('mysql')->commit();
        } catch (\Exception $e) {
            DB::connection('mysql')->rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'شارژ های مورد نظر با موفقیت تغییر یافتند.',
        ], 200);
    }

    public function destroy($id)
    {
        $unit = auth()->buildingManager()->building->units()->where('id', $id)->first();
        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => "Not found"
            ], 404);
        }
        $this->detachResidents($unit, $unit->residents);
        $unit->delete();
        return response()->json([
            'success' => true,
            'message' => __("واحد با موفقیت حذف شد"),
        ], 200);
    }

    public function multipleDestroy(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'ids' => 'array|required',
            'ids.*' => 'numeric|exists:building_units,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        foreach ($request->ids as $id) {
            $unit = auth()->buildingManager()->building->units()->where('id', $id)->first();
            if (!$unit) {
                return response()->json([
                    'success' => false,
                    'message' => "Not found"
                ], 404);
            }
            $this->detachResidents($unit, $unit->residents);
            $unit->delete();
        }
        return response()->json([
            'success' => true,
            'message' => __("واحد با موفقیت حذف شد"),
        ], 200);
    }

    public function accountingReports()
    {
        $building = auth()->buildingManager()->building;
        if ($building->name_en !== 'jamtower') {
            return response()->json([
                'success' => false,
                'message' => "Not found"
            ], 404);
        }
        return response()->download(resource_path('jamtower.zip'));
    }

    private function detachResidents($unit, $residents)
    {
        $residents_records = DB::connection('mysql')
            ->table('building_units_users')
            ->where('building_unit_id', $unit->id)
            ->whereIn('user_id', collect($residents)->pluck('id')->toArray())
            ->whereNull('deleted_at')
            ->get();

        $residents_records->each(function ($record) {
            DB::connection('mysql')
                ->table('building_units_users')
                ->where('id', $record->id)
                ->update(['deleted_at' => now()]);
        });
    }
}