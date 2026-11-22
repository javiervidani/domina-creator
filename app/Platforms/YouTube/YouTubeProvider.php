<?php

namespace App\Platforms\YouTube;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\User;

// Google sign-in that asks only for YouTube access (no profile or email), so the "user" is the channel itself.
// Offline access gives a refresh token: Google's access tokens last one hour.
class YouTubeProvider extends AbstractProvider
{
    public const READ = 'https://www.googleapis.com/auth/youtube.readonly';
    public const CAPTIONS = 'https://www.googleapis.com/auth/youtube.force-ssl';

    protected $scopes = [self::READ];

    protected $scopeSeparator = ' ';

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase('https://accounts.google.com/o/oauth2/v2/auth', $state);
    }

    protected function getTokenUrl(): string
    {
        return 'https://oauth2.googleapis.com/token';
    }

    protected function getUserByToken($token): array
    {
        $response = $this->getHttpClient()->get('https://www.googleapis.com/youtube/v3/channels', [
            'query' => ['part' => 'snippet', 'mine' => 'true'],
            'headers' => ['Authorization' => 'Bearer '.$token],
        ]);
        $channel = json_decode((string) $response->getBody(), true)['items'][0] ?? null;

        return $channel ? ['id' => $channel['id'], 'title' => $channel['snippet']['title'] ?? $channel['id']] : [];
    }

    protected function mapUserToObject(array $user): User
    {
        return (new User)->setRaw($user)->map(['id' => $user['id'] ?? null, 'nickname' => $user['title'] ?? null]);
    }
}
