<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserAuth;
use Illuminate\Support\Facades\Hash;
use App\Providers\RouteServiceProvider;

class FirstLoginController extends Controller
{
    public function index()
    {
        return view('auth.first_login_change_password');
    }

    public function store(Request $request)
    {
        $request->validate([
            'password' => 'required|string|alpha_num|confirmed|min:8',
        ]);

        $user = auth()->user();
        
        $user->forceFill([
            'password' => Hash::make($request->password),
            'is_first_login' => false,
        ])->save();

        return redirect(RouteServiceProvider::HOME);
    }
}
