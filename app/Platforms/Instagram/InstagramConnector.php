<?php

namespace App\Platforms\Instagram;

use App\Platforms\BaseConnector;
use App\Platforms\PlatformException;
use Illuminate\Support\Facades\Http;

// Same calls as the old cnx-instagram-sync plugin (instagram-api.php, instagram-token.php).
class InstagramConnector extends BaseConnector
{
    private const HOST = 'https://graph.instagram.com';

    public static function key(): string
    {
        return 'instagram';
    }

    public function mediaPage(string $after = '', int $limit = 25): array
    {
        $query = [
            'fields' => 'id,media_type,media_url,thumbnail_url,caption,timestamp,permalink',
            'limit' => max(1, min(50, $limit)),
            'access_token' => $this->accessToken,
        ];
        if ($after !== '') {
            $query['after'] = $after;
        }

        $body = $this->get(self::HOST.'/v23.0/me/media', $query);

        return [
            'items' => $body['data'] ?? [],
            'after' => (string) ($body['paging']['cursors']['after'] ?? ''),
            'has_next' => ! empty($body['paging']['next']),
        ];
    }

    // The host root, not the versioned path. Instagram refuses tokens under 24 hours old or already expired.
    public function refreshToken(): array
    {
        $body = $this->get(self::HOST.'/refresh_access_token', [
            'grant_type' => 'ig_refresh_token',
            'access_token' => $this->accessToken,
        ]);

        if (empty($body['access_token'])) {
            throw new PlatformException('Instagram did not return a refreshed token.');
        }

        return ['access_token' => $body['access_token'], 'expires_in' => (int) ($body['expires_in'] ?? 60 * 86400)];
    }

    private function get(string $url, array $query): array
    {
        $response = Http::timeout(30)->get($url, $query);
        $body = $response->json() ?? [];

        if ($response->failed()) {
            $code = (int) ($body['error']['code'] ?? 0);
            // Never include the request URL here: it carries the token.
            throw new PlatformException($body['error']['message'] ?? 'Instagram request failed.', $code === 190);
        }

        return $body;
    }
}
