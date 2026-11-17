<?php

namespace App\Platforms\Instagram;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\User;

// Socialite provider for Instagram Login (not the retired Basic Display API). It also swaps the short-lived
// token for the 60-day one right away, as the old plugin's settings flow did.
class InstagramProvider extends AbstractProvider
{
    protected $scopes = ['instagram_business_basic'];

    protected $scopeSeparator = ',';

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase('https://www.instagram.com/oauth/authorize', $state);
    }

    protected function getTokenUrl(): string
    {
        return 'https://api.instagram.com/oauth/access_token';
    }

    public function getAccessTokenResponse($code): array
    {
        $short = parent::getAccessTokenResponse($code);
        // Newer responses wrap the fields in a `data` list.
        $short = $short['data'][0] ?? $short;

        $long = $this->getHttpClient()->get('https://graph.instagram.com/access_token', ['query' => [
            'grant_type' => 'ig_exchange_token',
            'client_secret' => $this->clientSecret,
            'access_token' => $short['access_token'],
        ]]);
        $long = json_decode((string) $long->getBody(), true);

        return ['access_token' => $long['access_token'], 'expires_in' => $long['expires_in'] ?? 60 * 86400,
            'user_id' => $short['user_id'] ?? null];
    }

    protected function getUserByToken($token): array
    {
        $response = $this->getHttpClient()->get('https://graph.instagram.com/v23.0/me', ['query' => [
            'fields' => 'user_id,username', 'access_token' => $token,
        ]]);

        return json_decode((string) $response->getBody(), true);
    }

    protected function mapUserToObject(array $user): User
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['user_id'] ?? $user['id'],
            'nickname' => $user['username'] ?? null,
        ]);
    }
}
