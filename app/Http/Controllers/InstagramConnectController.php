<?php

namespace App\Http\Controllers;

use App\Models\ConnectedAccount;
use App\Platforms\Instagram\InstagramProvider;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class InstagramConnectController extends Controller
{
    public function redirect()
    {
        // An empty id sends the creator to a dead Instagram page, so say what is missing here instead.
        foreach (['client_id', 'client_secret', 'redirect'] as $key) {
            if (! config('services.instagram.'.$key)) {
                return redirect()->route('accounts')->with('error', 'Instagram is not set up on the server yet (missing '.$key.').');
            }
        }

        return Socialite::buildProvider(InstagramProvider::class, config('services.instagram'))->redirect();
    }

    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('accounts')->with('error', 'Instagram was not connected: '.$request->query('error_reason', 'cancelled'));
        }

        try {
            $user = Socialite::buildProvider(InstagramProvider::class, config('services.instagram'))->user();
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('accounts')->with('error', 'Instagram did not accept the sign-in. Try again.');
        }

        $first = ConnectedAccount::count() === 0;
        $account = ConnectedAccount::updateOrCreate(
            ['platform' => 'instagram', 'external_id' => (string) $user->getId()],
            ['handle' => (string) $user->getNickname(), 'status' => 'ok', 'last_error' => null],
        );
        if ($first) {
            $account->update(['is_source_of_truth' => true]); // the first account is the source until the creator moves it
        }

        $account->token()->updateOrCreate([], [
            'access_token' => $user->token,
            'expires_at' => now()->addSeconds((int) $user->expiresIn),
            'refreshed_at' => now(),
        ]);

        return redirect()->route('accounts')->with('status', 'Instagram connected.');
    }
}
