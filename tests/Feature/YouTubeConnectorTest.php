<?php

namespace Tests\Feature;

use App\Platforms\PlatformException;
use App\Platforms\YouTube\YouTubeConnector;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

// Fake Google answers only: no real token, channel or video.
class YouTubeConnectorTest extends TestCase
{
    public function test_the_uploads_page_is_shaped_like_an_instagram_page_and_skips_private_videos(): void
    {
        Http::fake(['www.googleapis.com/youtube/v3/playlistItems*' => Http::response([
            'nextPageToken' => 'NEXT',
            'items' => [
                ['snippet' => ['title' => 'Hello', 'description' => 'World', 'thumbnails' => ['high' => ['url' => 'https://i.ytimg.com/a.jpg']]],
                    'contentDetails' => ['videoId' => 'vid1', 'videoPublishedAt' => '2026-01-02T03:04:05Z']],
                ['snippet' => ['title' => 'Private video', 'description' => '', 'thumbnails' => []], 'contentDetails' => ['videoId' => 'vid2']],
            ],
        ])]);

        $page = (new YouTubeConnector('token', 'UCabc123'))->mediaPage();

        $this->assertCount(1, $page['items']);
        $this->assertSame("Hello\n\nWorld", $page['items'][0]['caption']);
        $this->assertSame('https://www.youtube.com/watch?v=vid1', $page['items'][0]['permalink']);
        $this->assertSame('NEXT', $page['after']);
        $this->assertTrue($page['has_next']);
        Http::assertSent(fn ($r) => str_contains($r->url(), 'playlistId=UUabc123'));
    }

    public function test_a_missing_captions_permission_says_what_to_press(): void
    {
        Http::fake(['www.googleapis.com/*' => Http::response(['error' => ['code' => 403, 'errors' => [['reason' => 'insufficientPermissions']]]], 403)]);

        try {
            (new YouTubeConnector('token', 'UCabc123'))->captionTracks('vid1');
            $this->fail('expected a PlatformException');
        } catch (PlatformException $e) {
            $this->assertStringContainsString('Allow transcripts', $e->getMessage());
        }
    }
}
