<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ForcePasswordChangeController extends Controller
{
    public function edit()
    {
        return view('auth.force-change-password');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = $request->user();

        $user->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
            'password_changed_at' => now(),
        ]);

if (! $user->two_factor_confirmed_at) {
    return redirect()->route('two-factor.setup')
        ->with('warning', 'Configurez l’authentification à deux facteurs.');
}

return redirect('/admin')->with(
    'success',
    'Mot de passe modifié avec succès.'
);    }
}