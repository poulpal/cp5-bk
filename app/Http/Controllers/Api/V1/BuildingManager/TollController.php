<?php

namespace App\Http\Controllers\Api\V1\BuildingManager;

use App\Http\Controllers\Controller;
use App\Http\Resources\BuildingManager\TollResource;
use App\Models\Building;
use App\Models\BuildingManager;
use App\Models\BuildingUnit;
use App\Models\Commission;
use App\Models\Toll;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Morilog\Jalali\Jalalian;

class TollController extends Controller
{

    public function __construct()
    {
        $this->middleware('restrictBuildingManager:other')->except(['index']);
        $this->middleware('restrictBuildingManager:hsh-1')->except(['index', 'cashPay']);
    }

    public function index(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'unit' => 'nullable|numeric|exists:building_units,id',
            'filter' => 'nullable|in:all,unverified,verified',
            'type' => 'nullable|in:toll,deposit,cost,income',
            'perPage' => 'nullable|numeric',
            'paginate' => 'nullable|boolean',
            'sort' => 'nullable|string',
            'order' => 'nullable|string',
            'search' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $tolls = auth()->buildingManager()->building->tolls()->with('service');
        $tolls = $tolls->whereNot('serviceable_type', Commission::class);

        if (request()->has('unit')) {
            $tolls = $tolls->where('serviceable_id', request()->unit)->where('serviceable_type', 'App\Models\BuildingUnit');
        }

        if (request()->has('filter')) {
            if (request()->filter == 'unverified') {
                $tolls = $tolls->where('is_verified', 0);
            } elseif (request()->filter == 'verified') {
                $tolls = $tolls->where('is_verified', 1);
            }
        }

        if (request()->has('sort') && request()->sort) {
            if ($request->sort == 'unit_number') {
                $tolls = $tolls->join('building_units', 'building_units.id', '=', 'tolls.serviceable_id')
                    ->orderBy('building_units.unit_number', $request->order ?? 'desc');
            } else {
                $tolls = $tolls->orderBy(request()->sort, request()->order ?? 'desc');
            }
        } else {
            $tolls = $tolls->orderBy('created_at', 'desc');
        }

        if (request()->has('search') && request()->search && request()->search != '') {
            $tolls = $tolls->whereHas('unit', function ($query) {
                $query->where('unit_number', 'like', '%' . request()->search . '%');
            });
        }

        $tolls_query = $tolls;

        if (request()->has('paginate') && request()->paginate) {
            $tolls = $tolls->paginate(request()->perPage ?? 20);
        } else {
            $tolls = $tolls->get();
        }

        // $balance = 0;

        // if (request()->has('paginate') && request()->paginate && $tolls->count() > 0) {
        //     $first_id = $tolls->sortBy('id')->first()->id;
        //     $balance = round($tolls_query->where('id', '<', $first_id)->sum('amount'), 1);
        // }

        // foreach ($tolls->reverse() as $toll) {
        //     $balance += $toll->amount;
        //     $toll->balance = round($balance, 1);
        // }

        if (request()->has('paginate') && request()->paginate) {
            return response()->paginate($tolls, TollResource::class);
        } else {
            return response()->json([
                'success' => true,
                'data' => [
                    'has_more' => false,
                    'tolls' => TollResource::collection($tolls),
                ]
            ]);
        }
    }

    public function cashPay(Toll $toll)
    {
        if ($toll->building_id != auth()->buildingManager()->building->id) {
            return response()->json([
                'success' => false,
                'message' => 'شما اجازه دسترسی به این تراکنش را ندارید.'
            ], 403);
        }

        if ($toll->status == 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'این تراکنش قبلا پرداخت شده است.'
            ], 422);
        }

        $toll->update([
            'status' => 'paid',
            'payment_method' => 'cash',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تراکنش با موفقیت تایید شد.'
        ]);
    }

    public function store(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'amount' => ['required', 'decimal:0,1', function ($attribute, $value, $fail) use ($request) {
                if ($value < 0) {
                    $fail(__("مبلغ وارد شده باید بزرگتر از صفر باشد"));
                }
            }],
            'description' => 'required',
            'date' => 'required|date',
            'resident_type' => 'required|in:owner,resident',
            'send_sms' => 'nullable|boolean', // ✅ پارامتر برای ارسال SMS
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $building = auth()->buildingManager()->building;
        $unit = $building->units()->where('id', $id)->first();
        
        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => "واحد یافت نشد"
            ], 404);
        }
        
        // ✅ بررسی اعتبار SMS اگر ارسال فعال باشد
        // طول پیامک معمولاً 2-3 واحد است، ما حداقل 3 واحد چک می‌کنیم
        $minSmsCredits = 3; // حداقل برای اطمینان
        
        if ($request->send_sms) {
            // تشخیص مخاطب بر اساس resident_type
            $recipient = $request->resident_type === 'owner' ? $unit->owner : ($unit->renter ?? $unit->owner);
            
            if (!$recipient) {
                return response()->json([
                    'success' => false,
                    'message' => 'مخاطبی برای ارسال پیامک یافت نشد. لطفا ابتدا ساکن یا مالک واحد را تعریف کنید.'
                ], 422);
            }
            
            if (!$recipient->mobile) {
                return response()->json([
                    'success' => false,
                    'message' => 'شماره موبایل مخاطب ثبت نشده است.'
                ], 422);
            }
            
            // ✅ بررسی موجودی SMS (حداقل 3 واحد)
            if ($building->sms_balance < $minSmsCredits) {
                return response()->json([
                    'success' => false,
                    'message' => "موجودی پیامک کافی نیست. موجودی فعلی: {$building->sms_balance}، حداقل مورد نیاز: {$minSmsCredits}"
                ], 422);
            }
        }
        
        // ✅ ایجاد لینک پرداخت (Toll)
        $toll = $unit->tolls()->create([
            'building_id' => $unit->building->id,
            'amount' => $request->amount,
            'payment_method' => 'cash',
            'serviceable_type' => 'App\Models\BuildingUnit',
            'serviceable_id' => $unit->id,
            'description' => $request->description,
            'resident_type' => $request->resident_type,
            'created_at' => Carbon::parse($request->date),
        ]);
        
        $smsWasSent = false;
        $actualSmsUnits = 0; // ✅ متغیر برای نگهداری واحدهای استفاده شده
        $landingUrl = config('app.landing_url', env('VITE_LANDING_URL', 'https://chargepal.ir'));
        
        // ✅ فرمت کردن مبلغ برای نمایش در پیامک
        $amountInRial = $request->amount * 10;
        $formattedAmount = number_format($amountInRial, 0, '', ','); // 5,000,000
        
        // ✅ ارسال SMS با متن ساده اگر فعال باشد
        if ($request->send_sms) {
            $recipient = $request->resident_type === 'owner' ? $unit->owner : ($unit->renter ?? $unit->owner);
            
            // ✅ ساخت متن پیامک
            $smsText = "سلام";
            if ($recipient->first_name) {
                $smsText .= " " . $recipient->first_name;
            }
            $smsText .= "\n";
            $smsText .= "واحد {$unit->unit_number} - {$building->name}\n";
            $smsText .= "مبلغ: {$formattedAmount} ریال\n";
            $smsText .= "{$request->description}\n";
            $smsText .= "لینک پرداخت:\n{$landingUrl}/p/{$toll->token}";
            
            // ✅ محاسبه طول واقعی پیامک (هر 70 کاراکتر = 1 واحد)
            $textLength = mb_strlen($smsText);
            $actualSmsUnits = max(1, ceil($textLength / 70));
            
            try {
                // ✅ ارسال SMS
                $recipient->notify(new \App\Notifications\User\TollPaymentLinkNotification(
                    $unit,
                    $toll,
                    $building->name,
                    $formattedAmount,
                    $request->description
                ));
                
                // ✅ کسر طول واقعی از اعتبار SMS ساختمان
                $building->sms_balance -= $actualSmsUnits;
                $building->save();
                
                // ✅ ثبت در جدول sms_messages برای تاریخچه
                \App\Models\SmsMessage::create([
                    'building_id' => $building->id,
                    'pattern' => "لینک پرداخت - واحد {$unit->unit_number} - طول: {$textLength} کاراکتر",
                    'units' => [$unit->id],
                    'length' => $actualSmsUnits, // طول واقعی (2-3 واحد معمولاً)
                    'count' => 1, // تعداد مخاطب
                    'resident_type' => $request->resident_type,
                    'status' => 'sent',
                ]);
                
                $smsWasSent = true;
                
            } catch (\Exception $e) {
                // در صورت خطا در ارسال SMS، لاگ می‌کنیم اما لینک پرداخت ایجاد شده است
                \Log::error('Error sending toll payment SMS: ' . $e->getMessage());
                \Log::error('Stack trace: ' . $e->getTraceAsString());
            }
        }

        return response()->json([
            'success' => true,
            'message' => __("لینک پرداخت با موفقیت ایجاد شد") . ($smsWasSent ? " و پیامک ارسال گردید." : "."),
            'data' => [
                'toll_id' => $toll->id,
                'payment_link' => $landingUrl . '/p/' . $toll->token,
                'sms_sent' => $smsWasSent,
                'sms_credits_used' => $actualSmsUnits, // ✅ واحدهای واقعی استفاده شده
            ]
        ], 201);
    }

    public function addMultiple(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tolls' => 'required|array',
            'tolls.*.unit_number' => 'required',
            // 'tolls.*.amount' => 'required|decimal:0,1|min:1',
            'tolls.*.amount' => function ($attribute, $value, $fail) {
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
            'tolls.*.description' => 'required',
            'tolls.*.date' => 'nullable|date',
            'resident_type' => 'required|in:owner,resident',
            'send_sms' => 'nullable|boolean', // ✅ پارامتر جدید
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $building = auth()->buildingManager()->building;
        $date_format = 'Y/m/d';
        
        // ✅ تخمین حداقل 3 واحد برای هر پیامک
        $estimatedCreditsPerMessage = 3;
        
        // ✅ بررسی موجودی SMS اگر ارسال فعال باشد
        if ($request->send_sms) {
            $recipientCount = 0;
            foreach ($request->tolls as $tollData) {
                $unit = $building->units()->where('unit_number', $tollData['unit_number'])->first();
                if ($unit) {
                    $recipient = $request->resident_type === 'owner' ? $unit->owner : ($unit->renter ?? $unit->owner);
                    if ($recipient && $recipient->mobile) {
                        $recipientCount++;
                    }
                }
            }
            
            if ($recipientCount == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'هیچ مخاطبی با شماره موبایل معتبر برای ارسال پیامک یافت نشد.'
                ], 422);
            }
            
            // ✅ محاسبه اعتبار تخمینی
            $estimatedCredits = $recipientCount * $estimatedCreditsPerMessage;
            
            if ($building->sms_balance < $estimatedCredits) {
                return response()->json([
                    'success' => false,
                    'message' => "موجودی پیامک کافی نیست. موجودی فعلی: {$building->sms_balance}، تخمین مورد نیاز: {$estimatedCredits} ({$recipientCount} مخاطب × {$estimatedCreditsPerMessage})"
                ], 422);
            }
        }

        DB::connection('mysql')->beginTransaction();

        try {
            $createdTolls = [];
            $smsCount = 0;
            $totalCreditsUsed = 0; // ✅ مجموع اعتبار استفاده شده
            $landingUrl = config('app.landing_url', env('VITE_LANDING_URL', 'https://chargepal.ir'));
            
            foreach ($request->tolls as $tollData) {
                $unit = $building->units()->where('unit_number', $tollData['unit_number'])->first();
                
                if (!$unit) {
                    throw new \Exception(__("واحد ") . $tollData['unit_number'] . __(" در ساختمان شما موجود نیست"));
                }
                
                // ایجاد Toll
                $toll = Toll::create([
                    'building_id' => $unit->building->id,
                    'amount' => $tollData['amount'],
                    'payment_method' => 'cash',
                    'serviceable_type' => 'App\Models\BuildingUnit',
                    'serviceable_id' => $unit->id,
                    'resident_type' => $request->resident_type,
                    'description' => $tollData['description'],
                    'created_at' => isset($tollData['date']) ? Jalalian::fromFormat($date_format, $tollData['date'])->toCarbon() : Carbon::now(),
                    'updated_at' => isset($tollData['date']) ? Jalalian::fromFormat($date_format, $tollData['date'])->toCarbon() : Carbon::now(),
                ]);
                
                $createdTolls[] = [
                    'unit' => $unit,
                    'toll' => $toll,
                ];
                
                // ✅ ارسال SMS با متن ساده اگر فعال باشد
                if ($request->send_sms) {
                    $recipient = $request->resident_type === 'owner' ? $unit->owner : ($unit->renter ?? $unit->owner);
                    
                    if ($recipient && $recipient->mobile) {
                        // ✅ فرمت کردن مبلغ به ریال با جداکننده
                        $amountInRial = $tollData['amount'] * 10;
                        $formattedAmount = number_format($amountInRial, 0, '', ','); // 5,000,000
                        
                        try {
                            // ✅ محاسبه طول پیامک برای این واحد
                            $smsText = "سلام";
                            if ($recipient->first_name) {
                                $smsText .= " " . $recipient->first_name;
                            }
                            $smsText .= "\n";
                            $smsText .= "واحد {$unit->unit_number} - {$building->name}\n";
                            $smsText .= "مبلغ: {$formattedAmount} ریال\n";
                            $smsText .= "{$tollData['description']}\n";
                            $smsText .= "لینک پرداخت:\n{$landingUrl}/p/{$toll->token}";
                            
                            $textLength = mb_strlen($smsText);
                            $smsUnits = max(1, ceil($textLength / 70));
                            
                            // ✅ ارسال SMS
                            $recipient->notify(new \App\Notifications\User\TollPaymentLinkNotification(
                                $unit,
                                $toll,
                                $building->name,
                                $formattedAmount,
                                $tollData['description']
                            ));
                            
                            $smsCount++;
                            $totalCreditsUsed += $smsUnits; // واحد واقعی این پیامک
                            
                        } catch (\Exception $e) {
                            \Log::error('Error sending toll SMS for unit ' . $unit->unit_number . ': ' . $e->getMessage());
                            \Log::error('Stack trace: ' . $e->getTraceAsString());
                        }
                    }
                }
            }
            
            // ✅ کسر از اعتبار SMS (مجموع واقعی)
            if ($request->send_sms && $totalCreditsUsed > 0) {
                $building->sms_balance -= $totalCreditsUsed;
                $building->save();
                
                // ✅ ثبت در لاگ SMS
                \App\Models\SmsMessage::create([
                    'building_id' => $building->id,
                    'pattern' => "لینک پرداخت گروهی - {$smsCount} واحد",
                    'units' => collect($createdTolls)->pluck('unit.id')->toArray(),
                    'length' => $totalCreditsUsed, // مجموع اعتبار استفاده شده
                    'count' => $smsCount, // تعداد مخاطبان
                    'resident_type' => $request->resident_type,
                    'status' => 'sent',
                ]);
            }
            
            DB::connection('mysql')->commit();
            
        } catch (\Exception $e) {
            DB::connection('mysql')->rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }

        $message = 'لینک پرداخت با موفقیت ایجاد شد.';
        if ($request->send_sms && $smsCount > 0) {
            $message .= " تعداد {$smsCount} پیامک ارسال شد ({$totalCreditsUsed} واحد اعتبار).";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'total_tolls' => count($createdTolls),
                'sms_sent' => $smsCount,
                'sms_credits_used' => $totalCreditsUsed, // ✅ مجموع اعتبار
            ]
        ], 200);
    }

    public function destroy(Toll $toll)
    {
        if ($toll->building_id != auth()->buildingManager()->building->id) {
            return response()->json([
                'success' => false,
                'message' => 'شما اجازه دسترسی به این تراکنش را ندارید.'
            ], 403);
        }

        if ($toll->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'تراکنش قابل حذف نیست.'
            ], 422);
        }

        $toll->delete();

        return response()->json([
            'success' => true,
            'message' => 'تراکنش با موفقیت حذف شد.'
        ]);
    }

    public function multipleDestroy(Request $request)
    {

        $validator = Validator::make(request()->all(), [
            'ids' => 'array|required',
            'ids.*' => 'numeric|exists:tolls,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        foreach ($request->ids as $id) {
            $toll = Toll::find($id);
            if ($toll->building_id != auth()->buildingManager()->building_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Toll not found'
                ], 404);
            }

            if ($toll->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'امکان حذف فاکتور های پرداخت شده وجود ندارد.'
                ], 422);
            }

            DB::connection('mysql')->beginTransaction();

            try {
                $toll->delete();
                DB::connection('mysql')->commit();
            } catch (\Exception $e) {
                DB::connection('mysql')->rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'خطایی در حذف فاکتور رخ داده است.'
                ], 500);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'فاکتور با موفقیت حذف شد.'
        ]);
    }
}