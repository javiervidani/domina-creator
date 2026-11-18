<?php

namespace App\Settings;

use Illuminate\Support\Facades\Http;
use Throwable;

class AiKeyTester
{
    /** @return array{ok: bool, error: ?string} the error is the provider's own words, never the key */
    public function test(string $provider, string $key): array
    {
        if (! AiProviders::valid($provider)) {
            return ['ok' => false, 'error' => 'Unknown provider.'];
        }

        try {
            $response = Http::timeout(20)->withHeaders(AiProviders::headers($provider, $key))->get(AiProviders::ALL[$provider]['url']);
        } catch (Throwable) {
            return ['ok' => false, 'error' => 'Could not reach the provider.'];
        }

        if ($response->successful()) {
            return ['ok' => true, 'error' => null];
        }

        $message = $response->json('error.message') ?? $response->json('error');
        $message = is_string($message) ? str_replace($key, '[key]', $message) : 'The provider said '.$response->status().'.';

        return ['ok' => false, 'error' => mb_substr($message, 0, 200)];
    }
}
