<?php

namespace App\Http\Controllers\Auth;

use App\Facades\NumberFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\OtpNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{

    protected $redirectTo = '/';

    public function __construct()
    {
        // get all guards 
        $guards = array_keys(config('auth.guards'));
        foreach ($guards as $guard) {
            if ($guard == 'sanctum') {
                continue;
            }
            $this->middleware('guest:' . $guard)->except('logout');
        }
        // $this->middleware('guest:user')->except('logout');
        // $this->middleware('guest:vendor')->except('logout');
        // $this->middleware('guest:admin')->except('logout');
        // $this->middleware('throttle:5,15')->only('sendOtp');
    }

    public function showLoginForm()
    {

        if (!session()->has('url.intended')) {
            session(['url.intended' => url()->previous()]);
        }

        return view('auth.login');
    }

    public function sendOtp(Request $request)
    {
        $request->merge([
            'mobile' => NumberFormatter::enDigits($request->mobile),
        ]);
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|regex:/(09)[0-9]{9}/'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }

        $mobile = $request->mobile;
        $user = User::where([['mobile', '=', $mobile]])->first();
        if ($user) {
            $generated_otp = random_int(100000, 999999);
            $ttl = 2;
            User::where([['mobile', '=', $mobile]])->update(['otp' => $generated_otp, 'otp_expires_at' => Carbon::now()->addMinutes($ttl)]);
            $user->notify(new OtpNotification($generated_otp));
            $request->session()->flash('mobile', $mobile);
            return redirect()->route('login');
        } else {
            // create new user
            $generated_otp = random_int(100000, 999999);
            $ttl = 2;
            $user = User::create([
                'mobile' => $mobile,
                'otp' => $generated_otp,
                'otp_expires_at' => Carbon::now()->addMinutes($ttl),
            ]);
            $user->notify(new OtpNotification($generated_otp));
            $request->session()->flash('mobile', $mobile);
            return redirect()->route('login');
        }
    }

    public function loginWithOtp(Request $request)
    {
        if (
            method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)
        ) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }

        $request->merge([
            'mobile' => NumberFormatter::enDigits($request->mobile),
        ]);
        $request->merge([
            'otp' => NumberFormatter::enDigits($request->otp),
        ]);
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|regex:/(09)[0-9]{9}/',
            'otp' => 'required|numeric|min:6',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }

        $mobile = $request->mobile;
        $user = User::where([['mobile', '=', request('mobile')]])->first();
        $expire = $user->otp_expires_at;

        if (Carbon::now() < $expire) {
            if ($request->otp !== $user->otp) {
                $request->session()->flash('mobile', $mobile);
                return redirect()->route('login')->withErrors(['otp' => 'کد وارد شده اشتباه است']);
            }
            if ($request->otp === $user->otp) {
                Auth::guard($user->role)->login($user, true);
                Auth::setUser($user);
                User::where('mobile', '=', $request->mobile)->update(['otp' => null]);
                return redirect()->route("$user->role.dashboard");
            }
        } else {
            User::where('mobile', '=', $request->mobile)->update(['otp' => null]);
            $request->session()->flash('mobile', null);
            return redirect()->route('login')->withErrors(['mobile' => 'کد وارد شده منقضی شده است']);
        }
        $this->incrementLoginAttempts($request);
        return $this->sendFailedLoginResponse($request);
    }

    public function logout(Request $request)
    {

        // Auth::logout();
        $guards = array_keys(config('auth.guards'));
        foreach ($guards as $guard) {
            if ($guard == 'sanctum') {
                continue;
            }
            auth($guard)->logout();
        }

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return redirect('/');
    }

    public function loggedOut(Request $request)
    {
        //
    }
}
