<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Mail\CustomMail;
use App\Mail\NewBuildingMail;
use App\Models\Building;
use App\Models\BuildingManager;
use App\Models\Poulpal\PoulpalBusiness;
use App\Models\Poulpal\PoulpalBusinessManager;
use App\Models\Poulpal\PoulpalUser;
use App\Models\User;
use App\Notifications\BuildingManager\RegisterCompleted;
use App\Notifications\BuildingManager\RegisterVerified;
use App\Rules\NationalId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Morilog\Jalali\Jalalian;

class BusinessRegisterController extends Controller
{
    public function register(Request $request)
    {
        if (config('app.type') == 'c36') {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'mobile' => 'required|regex:/(09)[0-9]{9}/',
                'phone_number' => 'required',
                'national_id' => ['nullable', 'numeric', new NationalId, 'unique:details,national_id'],
                'type' => 'required|in:building_manager',
                'building_name' => 'requiredif:type,building_manager',
                'building_name_en' => 'requiredif:type,building_manager|unique:buildings,name_en',
                'province' => 'required',
                'city' => 'required',
                'district' => 'required|numeric',
                'address' => 'required',
                'postal_code' => 'nullable|regex:/[0-9]{10}/',
                'email' => 'nullable|email|unique:details',
                'sheba_number' => 'nullable|regex:/[0-9]{24}/',
                'card_number' => 'nullable|regex:/[0-9]{16}/',
                'national_card_image' => 'nullable|mimes:png,jpg,jpeg|max:2048',
                'image' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
                'referral_mobile' => 'nullable|different:mobile',
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'mobile' => 'required',
                'phone_number' => 'nullable',
                'national_id' => ['nullable', 'numeric', new NationalId, 'unique:details,national_id'],
                'unit_count' => 'required|numeric|min:1',
                'type' => 'required|in:building_manager',
                'building_name' => 'requiredif:type,building_manager',
                'building_name_en' => 'requiredif:type,building_manager|unique:buildings,name_en',
                'province' => 'nullable',
                'city' => 'required',
                'district' => 'nullable|numeric',
                'address' => 'required',
                'postal_code' => 'nullable|regex:/[0-9]{10}/',
                'email' => 'nullable|email|unique:details',
                'sheba_number' => 'nullable|regex:/[0-9]{24}/',
                'card_number' => 'nullable|regex:/[0-9]{16}/',
                'national_card_image' => 'nullable|mimes:png,jpg,jpeg|max:2048',
                'image' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
                'referral_mobile' => 'nullable|different:mobile',
            ]);
        }

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $request->merge([
            'building_name_en' => trim($request->building_name_en),
        ]);
        $role = 'building_manager';

        $user = auth()->user();

        if ($user && $user->role == 'building_manager') {
            return response()->json([
                'success' => false,
                'errors' => [
                    'mobile' => 'شماره موبایل قبلا ثبت شده است.'
                ]
            ], 422);
        }

        if ($user && $user->role == 'user') {
            $user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'role' => $role,
            ]);
        }

        if (!$user) {
            // create user
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'mobile' => $request->mobile,
                'role' => $role,
            ]);
        }

        // $national_card_image_name = $request->national_card_image->store('public/national_card_images');
        // $url = Storage::url($national_card_image_name);

        $building_manager = BuildingManager::find($user->id);

        $building_manager->details()->create([
            'phone_number' => $request->phone_number,
            'national_id' => $request->national_id,
            'type' => $request->type,
            'province' => $request->province,
            'city' => $request->city,
            'district' => $request->district,
            'address' => $request->address,
            'postal_code' => $request->postal_code,
            'email' => $request->email,
            'sheba_number' => $request->sheba_number,
            'card_number' => $request->card_number,
            'national_card_image' => null,
        ]);

        if ($request->type == 'building_manager') {
            $building = $building_manager->building()->create([
                'name' => $request->building_name,
                'name_en' => $request->building_name_en,
                'start_charge_date' => $this->getFirstDayOfNextMonth(),
                'plan_slug' => 'free',
                'plan_duration' => '7',
                'plan_expires_at' => now()->addDays(7)->endOfDay(),
                // 'is_verified' => 1, // TODO: remove after promotion
                'signed_contract' => 0,
                'unit_count' => $request->unit_count ?? 1,
            ]);

            $building_manager->update([
                'building_id' => $building->id,
                'building_manager_type' => 'main'
            ]);

            if ($request->image) {
                $image = $request->image->store('public/building_images');
                $image_url = Storage::url($image);
                $building_manager->building()->update([
                    'image' => $image_url,
                ]);
            }

            $building->options()->create();
        }

        if (config('app.type') == 'c36') {
            $building->update([
                'is_verified' => 1,
                'signed_contract' => 1,
            ]);
        }

        $reffral_text = "شماره موبایل معرفی کننده: " . ($request->referral_mobile ?? "");

        if ($request->referral_mobile && $request->referral_mobile != 'undefined') {
            $referral_user = User::where('mobile', $request->referral_mobile)->firstOrNew([
                'mobile' => $request->referral_mobile,
            ]);
            $referral_user->save();
            $referral_user->referrals()->create([
                'building_id' => $building->id,
            ]);
        }

        if (config('app.type') == 'main') {
            $building_manager->notify(new RegisterCompleted());
            // try {
            //     $building_manager->notify((new RegisterVerified())->delay(now()->addMinutes(rand(10, 20)))); // TODO: remove after promotion
            // } catch (\Exception $e) {
            //     Log::error($e->getMessage());
            // }
        }
        $details = $building_manager->details;

        $token = $user->createToken('authToken')->plainTextToken;
try {
        Mail::to(['cc2com.com@gmail.com', 'arcenciel.ir@gmail.com', 'saman.moayeri@gmail.com', 'poulpal33@poulpal.com'])->send(new NewBuildingMail(
            'ساختمان جدید ثبت شد - ' . $building->name,
            "
            کسب و کار جدید ثبت شده است. <br>
            نام کسب و کار: $building->name - $building->name_en <br>
            تعداد واحد ها: $building->unit_count <br>
            نام مدیر: $user->first_name $user->last_name <br>
            شماره موبایل: $user->mobile <br>
            آدرس: $details->address <br>
            کد پستی: $details->postal_code <br>
            ایمیل کسب و کار: $details->email <br>
            شماره شبا: $details->sheba_number <br>
            شماره کارت: $details->card_number <br>
            تاریخ ثبت نام: " . Jalalian::now()->format('Y-m-d H:i:s') . "<br>" . $reffral_text .
                "userAgent: " . $request->header('User-Agent') . "<br>" .
                $request->national_card_image ?? null,
        ));
        } catch (\Exception $e) {
    Log::error('Mail failed: ' . $e->getMessage());
}

        $sheba = '';

        try {
            $sheba = $building->mainBuildingManagers()->first()->details->sheba_number;
        } catch (\Throwable $th) {
            Log::error($th);
        }

        $max_code = $building->accountingDetails()->max('code') ?? 100000;
        $code = $max_code + 1;

        $building->accountingDetails()->create([
            'name' => 'بانک IR' . str($sheba),
            'code' => $code,
            'type' => 'bank',
        ]);

        return response()->json([
            'success' => true,
            'message' => __("ثبت نام با موفقیت انجام شد"),
            'data' => [
                'token' => $token,
                'base_module' => 'base-16'
            ]
        ], 200);
    }

    private function getFirstDayOfNextMonth()
    {
        return Jalalian::now()
            ->addDays(-Jalalian::now()->format('d') + 1)
            ->addMonths(1)
            ->toCarbon()
            ->startOfDay()
            ->toDateTimeString();
    }

    private function handleNewBusiness($user, $request, $building)
    {
        return;
        try {
            $poulpal_user = PoulpalUser::where('mobile', $user->mobile)->firstOrNew([
                'mobile' => $user->mobile,
            ]);
            $poulpal_user->first_name = $user->first_name;
            $poulpal_user->last_name = $user->last_name;
            $poulpal_user->role = 'businessManager';
            $poulpal_user->save();

            $poulpal_business_manager = PoulpalBusinessManager::find($poulpal_user->id);

            $poulpal_business = PoulpalBusiness::where('business_manager_id', $poulpal_business_manager->id)->firstOrNew([
                'business_manager_id' => $poulpal_business_manager->id,
            ]);

            $poulpal_business->title  = $request->building_name;
            $poulpal_business->slug  = $request->building_name_en;
            $poulpal_business->phone_number  = $request->phone_number;
            $poulpal_business->national_id  = $request->national_id;
            $poulpal_business->province  = $request->province;
            $poulpal_business->city  = $request->city;
            $poulpal_business->district  = $request->district;
            $poulpal_business->address  = $request->address;
            $poulpal_business->postal_code  = $request->postal_code;
            $poulpal_business->email  = $request->email;
            $poulpal_business->sheba_number  = $request->sheba_number;
            $poulpal_business->card_number  = $request->card_number;
            $poulpal_business->type  = 'building';
            $poulpal_business->is_verified  = 0;

            $poulpal_business->save();

            $building->poulpal_business_id = $poulpal_business->id;
            $building->save();
        } catch (\Exception $e) {
            Mail::to('cc2com.com@gmail.com')->send(new CustomMail(__("خطا در ثبت ساختمان جدید"), $e->getMessage()));
            Log::error($e->getMessage());
        }
    }
}
