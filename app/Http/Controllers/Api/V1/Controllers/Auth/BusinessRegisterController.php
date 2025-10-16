<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\BuildingManager;
use App\Models\User;
use App\Notifications\OtpNotification;
use App\Providers\RouteServiceProvider;
use App\Rules\NationalId;
use App\Rules\Sheba;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Morilog\Jalali\Jalalian;

class BusinessRegisterController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $role = auth()->role();
        if (!$role) {
            return view('business.register');
        }
        return redirect('/');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'mobile' => 'required|regex:/(09)[0-9]{9}/|unique:users',
            'phone_number' => 'required',
            'national_id' => ['required', 'numeric', 'regex:/[0-9]{10}/', new NationalId],
            'type' => 'required|in:building_manager',
            'building_name' => 'requiredif:type,building_manager',
            'building_name_en' => 'requiredif:type,building_manager|unique:businesses,name_en',
            'province' => 'required',
            'city' => 'required',
            'district' => 'required',
            'address' => 'required',
            'postal_code' => 'required|regex:/[0-9]{10}/',
            'email' => 'required|email|unique:businesses',
            'sheba_number' => 'required|regex:/[0-9]{24}/',
            'card_number' => 'required',
            'national_card_image' => 'required|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors())->withInput();
        }

        $request->merge([
            'building_name_en' => trim($request->building_name_en),
        ]);
        $role = $request->type;

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'mobile' => $request->mobile,
            'role' => $role,
        ]);

        // store national card image
        $national_card_image_name = $request->national_card_image->store('public/national_card_images');
        $url = Storage::url($national_card_image_name);

        $user->business()->create([
            'name' => $request->building_name,
            'name_en' => $request->building_name_en,
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
            'national_card_image' => $url,
        ]);

        if ($request->type == 'building_manager') {
            $building_manager = BuildingManager::find($user->id);
            $building_manager->building()->create([
                'start_charge_date' => $this->getFirstDayOfNextMonth(),
            ]);
        }

        $generated_otp = random_int(100000, 999999);
        $ttl = 2;

        User::where([['mobile', '=', $request->mobile]])->update(['otp' => $generated_otp, 'otp_expires_at' => Carbon::now()->addMinutes($ttl)]);
        $user->notify(new OtpNotification($generated_otp));
        $request->session()->flash('mobile', $request->mobile);
        return redirect()->route('login');


        return redirect()->route('login')->with('success', 'ثبت نام با موفقیت انجام شد.');

        return redirect(RouteServiceProvider::HOME);
    }

    private function getFirstDayOfCurrentMonth()
    {
        return Jalalian::now()
            ->addDays(-Jalalian::now()->format('d') + 1)
            ->toCarbon()
            ->startOfDay()
            ->toDateTimeString();
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
}
