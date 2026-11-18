<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Creator;
use App\Models\CreatorMember;
use App\Models\User;
use App\Tenancy\Provisioner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Throwable;

class RegisterController extends Controller
{
    public function show()
    {
        return view('auth.register');
    }

    public function store(Request $request, Provisioner $provisioner)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            // Not the `unique:` rule: it reads "public.users" as connection "public", so the check is done by hand.
            'email' => ['required', 'email', 'max:190', function ($attribute, $value, $fail) {
                if (User::where('email', $value)->exists()) {
                    $fail('This email already has an account.');
                }
            }],
            'password' => ['required', 'confirmed', Password::min(10)],
        ]);

        [$user, $creator] = DB::transaction(function () use ($data) {
            $user = User::create($data);
            $creator = Creator::create(['name' => $data['name']]);
            CreatorMember::create(['user_id' => $user->id, 'creator_id' => $creator->id, 'role' => 'owner']);

            return [$user, $creator];
        });

        try {
            $provisioner->provision($creator->id);
        } catch (Throwable $e) {
            // No half-made account: the sign-up can be retried with the same email.
            $creator->delete();
            $user->delete();
            throw $e;
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home');
    }
}
