<?php

namespace App\Http\Controllers;

use App\Models\ConnectedAccount;
use App\Platforms\PlatformException;
use App\Platforms\Telegram\TelegramConnector;
use Illuminate\Http\Request;

// Telegram has no sign-in: the creator makes a bot, adds it to a channel or group, and gives us the bot token and chat.
// The token is checked with Telegram and stored encrypted; errors never echo it back.
class TelegramConnectController extends Controller
{
    public function store(Request $request)
    {
        $token = trim((string) $request->input('bot_token'));
        $chat = trim((string) $request->input('chat_id'));
        if ($token === '' || $chat === '') {
            return back()->with('error', 'Enter the bot token and the channel or group.');
        }

        try {
            $info = (new TelegramConnector($token, $chat))->chat();
        } catch (PlatformException $e) {
            return back()->with('error', 'Telegram did not accept that: '.$e->getMessage().' (the bot must be an admin of the channel).');
        }

        // A Telegram account is a place to publish to, never the source of truth or an import source.
        $account = ConnectedAccount::updateOrCreate(
            ['platform' => 'telegram', 'external_id' => $info['id']],
            ['handle' => $info['title'], 'status' => 'ok', 'last_error' => null, 'import_enabled' => false],
        );
        $account->token()->updateOrCreate([], ['access_token' => $token, 'expires_at' => null, 'refreshed_at' => now()]);

        return redirect()->route('accounts')->with('status', 'Telegram connected: '.$info['title'].'.');
    }
}
