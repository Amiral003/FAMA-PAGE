<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TwoFactorSetupController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        if ($user->two_factor_confirmed_at) {
            return redirect('/admin');
        }

        return view('auth.setup-2fa', [
            'user' => $user,
        ]);
    }
}