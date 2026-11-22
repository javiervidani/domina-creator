<?php

namespace App\Platforms\YouTube;

use App\Models\ConnectedAccount;
use App\Platforms\BaseConnector;
use App\Platforms\PlatformException;
use Illuminate\Support\Facades\Http;

// YouTube Data API v3, read-only: the channel's videos and the creator's own caption tracks.
class YouTubeConnector extends BaseConnector
{
    private const API = 'https://www.googleapis.com/youtube/v3';

    public function __construct(string $accessToken, private string $channelId = '')
    {
        parent::__construct($accessToken);
    }

    public static function key(): string
    {
        return 'youtube';
    }

    /** Google's access token lasts an hour, so it is swapped for a fresh one (from the refresh token) before each use. */
    public static function forAccount(ConnectedAccount $account): self
    {
        $token = $account->token;
        $response = Http::timeout(20)->asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => config('services.google.client_id'), 'client_secret' => config('services.google.client_secret'),
            'refresh_token' => $token->refresh_token, 'grant_type' => 'refresh_token',
        ]);
        if (! $token->refresh_token || ! $response->successful() || ! $response->json('access_token')) {
            $dead = ! $token->refresh_token || $response->json('error') === 'invalid_grant';
            throw new PlatformException($dead ? 'Google no longer allows this connection. Reconnect YouTube.' : 'Could not refresh the Google token. Try again.', $dead, ! $dead);
        }
        $token->update(['access_token' => $response->json('access_token'), 'refreshed_at' => now()]);

        return new self($response->json('access_token'), $account->external_id);
    }

    /** One page of the channel's uploads, shaped like an Instagram page so the same importer reads it. */
    public function mediaPage(string $after = '', int $limit = 50): array
    {
        // A channel's uploads playlist id is its channel id with UC swapped for UU.
        $body = $this->get('/playlistItems', ['part' => 'snippet,contentDetails', 'maxResults' => min(50, $limit),
            'playlistId' => 'UU'.substr($this->channelId, 2)] + ($after !== '' ? ['pageToken' => $after] : []));
        $items = [];
        foreach ($body['items'] ?? [] as $row) {
            $snippet = $row['snippet'] ?? [];
            $id = $row['contentDetails']['videoId'] ?? null;
            if (! $id || empty($snippet['thumbnails'])) { // private and deleted videos have no thumbnails
                continue;
            }
            $thumb = $snippet['thumbnails'];
            $items[] = ['id' => $id, 'media_type' => 'VIDEO', 'permalink' => 'https://www.youtube.com/watch?v='.$id,
                'caption' => trim(($snippet['title'] ?? '')."\n\n".($snippet['description'] ?? '')),
                'timestamp' => $row['contentDetails']['videoPublishedAt'] ?? $snippet['publishedAt'] ?? null,
                'thumbnail_url' => ($thumb['maxres'] ?? $thumb['high'] ?? $thumb['medium'] ?? $thumb['default'] ?? [])['url'] ?? null];
        }
        $next = (string) ($body['nextPageToken'] ?? '');

        return ['items' => $items, 'after' => $next, 'has_next' => $next !== ''];
    }

    /** @return array<array{id: string, language: string, kind: string}> the creator's own caption tracks */
    public function captionTracks(string $videoId): array
    {
        $body = $this->get('/captions', ['part' => 'snippet', 'videoId' => $videoId]);

        return collect($body['items'] ?? [])->map(fn ($t) => ['id' => $t['id'], 'language' => (string) ($t['snippet']['language'] ?? ''),
            'kind' => strtolower((string) ($t['snippet']['trackKind'] ?? 'standard'))])->all();
    }

    public function downloadCaption(string $trackId): string
    {
        return $this->get('/captions/'.$trackId, ['tfmt' => 'srt'], true);
    }

    private function get(string $path, array $query, bool $raw = false): array|string
    {
        $response = Http::timeout(30)->withToken($this->accessToken)->get(self::API.$path, $query);
        if ($response->failed()) {
            $error = $response->json('error') ?? [];
            $reason = $error['errors'][0]['reason'] ?? '';
            // Never include the request URL here: it is fine, but the message is shown to the creator as it stands.
            $message = match (true) {
                $response->status() === 401 => 'Google rejected the connection. Reconnect YouTube.',
                $reason === 'quotaExceeded' => 'YouTube\'s daily quota for this app is used up. Try again tomorrow.',
                $reason === 'insufficientPermissions' => 'YouTube needs your permission to read captions. On Accounts, press "Allow transcripts" and accept.',
                $response->status() === 403 => 'YouTube did not allow this ('.($reason ?: 'forbidden').'). Automatic captions often cannot be downloaded by apps.',
                $response->status() === 404 => 'YouTube could not find it.',
                default => (string) ($error['message'] ?? 'YouTube request failed.'),
            };
            throw new PlatformException($message, $response->status() === 401, $response->status() >= 500);
        }

        return $raw ? $response->body() : ($response->json() ?? []);
    }
}
