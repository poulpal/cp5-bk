<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Facades\NumberFormatter;
use App\Http\Controllers\Controller;
use App\Mail\CustomMail;
use App\Models\FcmToken;
use App\Models\Poulpal\PoulpalUser;
use App\Models\User;
use App\Notifications\OtpBackupNotification;
use App\Notifications\OtpNotification;
use App\Notifications\User\CustomNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Morilog\Jalali\Jalalian;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{

    use ThrottlesLogins;

    public function username()
    {
        return 'mobile';
    }

    public function sendOtp(Request $request)
    {
        $request->merge([
            'mobile' => NumberFormatter::enDigits($request->mobile),
        ]);

        // Set mobile validation rule based on locale
        $mobileValidationRule = app()->getLocale() === 'en'
            ? 'required|regex:/^(?:\+1)?\s*\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}$/'
            : 'required|regex:/(09)[0-9]{9}/';

        $validator = Validator::make($request->all(), [
            'mobile' => $mobileValidationRule,
            'hash' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $mobile = $request->mobile;
        $user = User::where([['mobile', '=', $mobile]])->first();
        if ($user) {
            if ($user->is_banned) {
                return response()->json([
                    'success' => false,
                    'message' => 'دسترسی به اکانت شما محدود شده است. لطفا با پشتیبانی تماس بگیرید',
                ], 403);
            }
            // if ($user->otp_expires_at > Carbon::now() && $user->otp && config('app.env') == 'production') {
            //     return response()->json([
            //         'success' => false,
            //         'message' => __("لطفا چند دقیقه دیگر دوباره تلاش کنید"),
            //         'data' => [
            //             'otp' => config('app.env') !== 'production' ? $user->otp : null,
            //             'otp_expires_at' => $user->otp_expires_at,
            //         ]
            //     ], 422);
            // }
            $generated_otp = random_int(1000, 9999);
            if (config('app.env') !== 'production') {
                $generated_otp = 2222;
            }
            $ttl = 2;
            User::where([['mobile', '=', $mobile]])->update(['otp' => $generated_otp, 'otp_expires_at' => Carbon::now()->addMinutes($ttl)]);
            $this->notifyUser($user, $generated_otp, $request->hash);
            return response()->json([
                'success' => true,
                'message' => __("رمز عبور با موفقیت ارسال شد"),
                'data' => [
                    'otp' => config('app.env') !== 'production' ? $generated_otp : null,
                    'otp_expires_at' => Carbon::now()->addMinutes($ttl),
                ]
            ], 200);
        } else {
            // create new user
            $generated_otp = random_int(1000, 9999);
            if (config('app.env') !== 'production') {
                $generated_otp = 2222;
            }
            $ttl = 2;
            $user = User::create([
                'mobile' => $mobile,
                'otp' => $generated_otp,
                'otp_expires_at' => Carbon::now()->addMinutes($ttl),
            ]);
            $this->handleNewUser($user);
            $this->notifyUser($user, $generated_otp, $request->hash);

            Mail::to(['arcenciel.ir@gmail.com', 'saman.moayeri@gmail.com', 'poulpal33@poulpal.com'])->send(
                new CustomMail(
                    'CHARGEPAL - ورود کاربر جدید - ' . $user->id,
                    "شماره موبایل: $user->mobile <br>
                    تاریخ: " . Jalalian::now()->format('Y-m-d H:i:s')
                )
            );
            return response()->json([
                'success' => true,
                'message' => __("رمز عبور با موفقیت ارسال شد"),
                'data' => [
                    'otp' => config('app.env') !== 'production' ? $generated_otp : null,
                    'otp_expires_at' => Carbon::now()->addMinutes($ttl),
                ]
            ], 200);
        }
    }

    private function notifyUser($user, $otp, $hash = null)
    {
        if (config('app.env') !== 'production') {
            return;
        }
        try {
            if ($hash == 'XFlUMTsT980') {
                $user->notify(new OtpNotification($otp, $hash));
            } else {
                $user->notify(new OtpNotification($otp));
            }
        } catch (\Throwable $th) {
            $user->notify(new OtpBackupNotification($otp));
        }
    }

    public function login(Request $request)
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

        // Set mobile validation rule based on locale
        $mobileValidationRule = app()->getLocale() === 'en'
            ? 'required|regex:/^(?:\+1)?\s*\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}$/'
            : 'required|regex:/(09)[0-9]{9}/';

        $validator = Validator::make($request->all(), [
            'mobile' => $mobileValidationRule,
            // ...existing rules...
            'otp' => ['required', function ($attribute, $value, $fail) use ($request) {
                if (!$request->usePassword) {
                    if (strlen($value) != 4) {
                        $fail('رمز عبور باید 4 رقم باشد');
                    }
                }
            }],
            'usePassword' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $mobile = $request->mobile;
        $otp = $request->otp;
        if ($request->usePassword) {
            $user = User::where([['mobile', '=', $mobile]])->first();
            if (!$user) {
                $this->incrementLoginAttempts($request);
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'otp' => __("رمز عبور اشتباه است")
                    ]
                ], 422);
            }
            if ($user) {
                if ($user->is_banned) {
                    return response()->json([
                        'success' => false,
                        'message' => 'دسترسی به اکانت شما محدود شده است. لطفا با پشتیبانی تماس بگیرید',
                    ], 403);
                }
                if (!\Hash::check($request->otp, $user->password)) {
                    return response()->json([
                        'success' => false,
                        'errors' => [
                            'otp' => __("رمز عبور اشتباه است")
                        ]
                    ], 422);
                }
                $token = $user->createToken('auth_token')->plainTextToken;

                if (is_null($user->last_login_at)) {
                    $this->firstLogin($user);
                }

                $user->last_login_at = now();
                $user->saveQuietly();
                return response()->json([
                    'success' => true,
                    'message' => __("ورود با موفقیت انجام شد"),
                    'data' => [
                        'token' => $token,
                        'user' => $user->only([
                            'first_name',
                            'last_name',
                            'mobile',
                            'role',
                        ]),
                    ]
                ], 200);
            }
        }
        $user = User::where([['mobile', '=', $mobile], ['otp', '=', $otp]])->first();
        if ($user) {
            if (Carbon::now()->gt($user->otp_expires_at)) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'otp' => __("رمز عبور منقضی شده است")
                    ]
                ], 422);
            }
            User::where('mobile', '=', $request->mobile)->update(['otp' => null]);
            $token = $user->createToken('auth_token')->plainTextToken;

            if (is_null($user->last_login_at)) {
                $this->firstLogin($user);
            }

            $user->last_login_at = now();
            $user->saveQuietly();
            return response()->json([
                'success' => true,
                'message' => __("ورود با موفقیت انجام شد"),
                'data' => [
                    'token' => $token,
                    'user' => $user->only([
                        'first_name',
                        'last_name',
                        'mobile',
                        'role',
                    ]),
                ]
            ], 200);
        } else {
            $this->incrementLoginAttempts($request);
            return response()->json([
                'success' => false,
                'errors' => [
                    'otp' => __("رمز عبور اشتباه است")
                ]
            ], 422);
        }
    }

    public function getMe(Request $request)
    {
        $user = auth()->user();
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user->only([
                    'first_name',
                    'last_name',
                    'mobile',
                    'role',
                ]),
            ]
        ], 200);
    }

    public function logout(Request $request)
    {
        $user = auth()->user();
        $user->tokens()->delete();
        return response()->json([
            'success' => true,
            'message' => __("خروج با موفقیت انجام شد"),
        ], 200);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6',
            'password_confirmation' => 'required|string|min:6|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => __("رمز عبور با موفقیت تغییر کرد"),
        ], 200);
    }


    public function registerFCMToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'data' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $token = FcmToken::updateOrCreate([
            'token' => $request->token,
        ], [
            'user_id' => auth()->user()->id,
            'data' => $request->data,
        ]);
        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => auth()->user()->id,
                    'mobile' => auth()->user()->mobile,
                    'first_name' => auth()->user()->first_name,
                    'last_name' => auth()->user()->last_name,
                    'role' => auth()->user()->role,
                ]
            ],
            'message' => __("توکن با موفقیت ثبت شد"),
        ], 200);
    }

    private function firstLogin($user)
    {
        $tokens = [618711, 220149];
        $token = $tokens[array_rand($tokens)];
        $user->notify(
            new CustomNotification(
                [
                    'NAME' => __("شارژپل"),
                ],
                $token
            )
        );
    }


    private function handleNewUser($user)
    {
        return;
        if (config('app.env') !== 'production') {
            return;
        }
        $poulpal_user = PoulpalUser::firstOrCreate(['mobile' => $user->mobile]);
        if ($poulpal_user->first_name == "" || $poulpal_user->last_name == "") {
            $poulpal_user->first_name = $user->first_name;
            $poulpal_user->last_name = $user->last_name;
            $poulpal_user->save();
        }
    }
}
