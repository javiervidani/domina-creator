<?php

namespace App\Http\Controllers;

use App\Models\AiKey;
use App\Settings\AiKeyTester;
use App\Settings\AiProviders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// The creator's own keys, one row per provider. A key is write-only: it can be replaced, tested or removed, never read back.
class AiKeyController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'provider' => ['required', 'string'],
            'api_key' => ['nullable', 'string', 'max:500'],
            'default_model' => ['nullable', 'string', 'max:100'],
        ]);
        abort_unless(AiProviders::valid($data['provider']), 404);

        $key = AiKey::firstOrNew(['provider' => $data['provider']]);
        $newKey = trim((string) ($data['api_key'] ?? ''));
        if (! $key->exists && $newKey === '') {
            return back()->with('error', 'Paste the API key.');
        }

        if ($newKey !== '') {
            $key->api_key = $newKey;
            $key->last_tested_at = $key->last_test_ok = $key->last_test_error = null; // a new key has not been tested
        }
        $key->default_model = trim((string) ($data['default_model'] ?? '')) ?: AiProviders::ALL[$data['provider']]['model'];
        $key->is_default = $key->exists ? $key->is_default : ! AiKey::where('is_default', true)->exists(); // the first key becomes the default
        $key->save();

        return back()->with('status', AiProviders::ALL[$data['provider']]['label'].' saved. Press Test key to check it.');
    }

    public function toggle(AiKey $aiKey)
    {
        $aiKey->update(['enabled' => ! $aiKey->enabled]);

        return back()->with('status', $aiKey->enabled ? 'Turned on.' : 'Turned off.');
    }

    public function makeDefault(AiKey $aiKey)
    {
        DB::transaction(function () use ($aiKey) {
            AiKey::where('is_default', true)->update(['is_default' => false]);
            $aiKey->update(['is_default' => true]);
        });

        return back()->with('status', 'Default provider changed.');
    }

    public function test(AiKey $aiKey, AiKeyTester $tester)
    {
        $result = $tester->test($aiKey->provider, $aiKey->api_key);
        $aiKey->update(['last_tested_at' => now(), 'last_test_ok' => $result['ok'], 'last_test_error' => $result['error']]);

        return back()->with($result['ok'] ? 'status' : 'error', $result['ok'] ? 'The key works.' : 'The key was not accepted: '.$result['error']);
    }

    public function destroy(AiKey $aiKey)
    {
        $wasDefault = $aiKey->is_default;
        $aiKey->delete();
        if ($wasDefault) {
            AiKey::orderBy('id')->first()?->update(['is_default' => true]);
        }

        return back()->with('status', 'Key removed.');
    }
}
