<?php

namespace App\Http\Controllers;

use App\Jobs\RefreshTokenJob;
use App\Models\ConnectedAccount;
use App\Platforms\Instagram\InstagramConnector;
use App\Platforms\PlatformException;
use Illuminate\Http\Request;

// The "I already have a token" route next to the normal sign-in. Errors never repeat the token back: a failed
// validation would otherwise flash it into the session.
class AccountTokenController extends Controller
{
    public function store(Request $request)
    {
        $token = trim((string) $request->input('token'));
        if (strlen($token) < 20) {
            return back()->with('error', 'Paste the full access token.');
        }

        try {
            $profile = (new InstagramConnector($token))->profile();
        } catch (PlatformException $e) {
            return back()->with('error', 'Instagram did not accept that token: '.$e->getMessage());
        }
        if ($profile['id'] === '') {
            return back()->with('error', 'Instagram did not say whose token this is.');
        }

        $first = ConnectedAccount::count() === 0;
        $account = ConnectedAccount::updateOrCreate(
            ['platform' => 'instagram', 'external_id' => $profile['id']],
            ['handle' => $profile['username'], 'status' => 'ok', 'last_error' => null],
        );
        if ($first) {
            $account->update(['is_source_of_truth' => true]);
        }

        // Expiry is unknown for a pasted token; the first refresh fills it in. refreshed_at = now keeps the weekly
        // job from trying inside Instagram's 24-hour window.
        $account->token()->updateOrCreate([], ['access_token' => $token, 'expires_at' => null, 'refreshed_at' => now()]);

        return redirect()->route('accounts')->with('status', 'Connected @'.$profile['username'].'. Press Refresh token after 24 hours to learn its expiry.');
    }

    public function refresh(Request $request, ConnectedAccount $account)
    {
        // After the response, outside the creator schema: the queue table is in public.
        RefreshTokenJob::dispatch($request->attributes->get('creator_id'), $account->id)->afterResponse();

        return back()->with('status', 'Token refresh queued. Instagram refuses tokens under 24 hours old.');
    }
}
