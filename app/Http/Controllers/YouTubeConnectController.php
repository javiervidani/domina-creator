<?php

namespace App\Http\Controllers;

use App\Models\ConnectedAccount;
use App\Platforms\YouTube\YouTubeProvider;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

// Connect the creator's own YouTube channel. Read-only at first; "Allow transcripts" asks again for the captions permission.
class YouTubeConnectController extends Controller
{
    private function provider(bool $transcripts = false)
    {
        $provider = Socialite::buildProvider(YouTubeProvider::class, config('services.google'));
        if ($transcripts) {
            $provider->setScopes([YouTubeProvider::READ, YouTubeProvider::CAPTIONS]);
        }

        // offline + consent: Google then returns a refresh token every time, which is how the connection survives past an hour.
        return $provider->with(['access_type' => 'offline', 'prompt' => 'consent', 'include_granted_scopes' => 'true']);
    }

    public function redirect(Request $request)
    {
        foreach (['client_id', 'client_secret', 'redirect'] as $key) {
            if (! config('services.google.'.$key)) {
                return redirect()->route('accounts')->with('error', 'YouTube is not set up on the server yet (missing GOOGLE_'.strtoupper($key === 'redirect' ? 'redirect_uri' : $key).').');
            }
        }

        return $this->provider($request->boolean('transcripts'))->redirect();
    }

    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('accounts')->with('error', 'YouTube was not connected: '.$request->query('error', 'cancelled'));
        }

        try {
            $user = $this->provider()->user();
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('accounts')->with('error', 'Google did not accept the sign-in. Try again.');
        }
        if (! $user->getId()) {
            return redirect()->route('accounts')->with('error', 'That Google account has no YouTube channel.');
        }

        $account = ConnectedAccount::updateOrCreate(
            ['platform' => 'youtube', 'external_id' => (string) $user->getId()],
            ['handle' => (string) $user->getNickname(), 'status' => 'ok', 'last_error' => null],
        );
        $token = $account->token()->firstOrNew([]);
        $token->fill(['access_token' => $user->token, 'expires_at' => null, 'refreshed_at' => now(),
            // Google only sends a refresh token when it asked for consent; keep the old one if it is missing.
            'refresh_token' => $user->refreshToken ?: $token->refresh_token])->save();

        return redirect()->route('accounts')->with('status', 'YouTube connected: '.$user->getNickname().'. Press Sync now to import your videos.');
    }
}
