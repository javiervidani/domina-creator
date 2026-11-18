<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Tenancy\Tenancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

// Users live in public, and a creator role cannot read public tables, so these updates step out of the schema.
class ProfileController extends Controller
{
    public function update(Request $request, Tenancy $tenancy)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'email' => ['required', 'email', 'max:190']]);
        $user = $request->user();

        $taken = $tenancy->outside(fn () => User::where('email', $data['email'])->where('id', '!=', $user->id)->exists());
        if ($taken) {
            return back()->withErrors(['email' => 'That email already has an account.']);
        }

        $tenancy->outside(fn () => $user->update($data));

        return back()->with('status', 'Profile saved.');
    }

    public function password(Request $request, Tenancy $tenancy)
    {
        $data = $request->validate(['current_password' => ['required'], 'password' => ['required', 'confirmed', Password::min(10)]]);
        $user = $request->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is wrong.']);
        }

        $tenancy->outside(fn () => $user->update(['password' => $data['password']]));

        return back()->with('status', 'Password changed.');
    }
}
