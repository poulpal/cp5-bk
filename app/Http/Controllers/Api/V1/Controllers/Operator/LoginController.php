<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function index()
    {
        if (auth()->guard('operator')->check()) {
            return redirect()->route('operator.dashboard');
        }
        return view('operator.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (auth()->guard('operator')->attempt($credentials, true)) {
            return redirect()->route('operator.dashboard');
        }

        return redirect()->route('operator.login.index')->with('error', 'اطلاعات وارد شده صحیح نمی باشد.');
    }

    public function logout()
    {
        auth()->guard('operator')->logout();
        return redirect()->route('operator.login.index');
    }
}
