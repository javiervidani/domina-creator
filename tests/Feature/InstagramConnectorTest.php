<?php

namespace Tests\Feature;

use App\Platforms\Instagram\InstagramConnector;
use App\Platforms\PlatformException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

// Fake HTTP only: no real token and no database.
class InstagramConnectorTest extends TestCase
{
    public function test_media_page_reads_items_and_cursor(): void
    {
        Http::fake(['graph.instagram.com/*' => Http::response([
            'data' => [['id' => '1']],
            'paging' => ['cursors' => ['after' => 'abc'], 'next' => 'x'],
        ])]);

        $page = (new InstagramConnector('t'))->mediaPage();

        $this->assertSame([['id' => '1']], $page['items']);
        $this->assertSame('abc', $page['after']);
        $this->assertTrue($page['has_next']);
    }

    public function test_refresh_returns_new_token(): void
    {
        Http::fake(['graph.instagram.com/refresh_access_token*' => Http::response(['access_token' => 'new', 'expires_in' => 5184000])]);

        $this->assertSame(['access_token' => 'new', 'expires_in' => 5184000], (new InstagramConnector('old'))->refreshToken());
    }

    public function test_dead_token_asks_for_reconnect_and_hides_the_token(): void
    {
        Http::fake(['*' => Http::response(['error' => ['code' => 190, 'message' => 'Session expired']], 400)]);

        try {
            (new InstagramConnector('secret-token'))->mediaPage();
            $this->fail('Expected an exception.');
        } catch (PlatformException $e) {
            $this->assertTrue($e->needsReconnect);
            $this->assertStringNotContainsString('secret-token', $e->getMessage());
        }
    }

    public function test_profile_returns_the_id_and_username(): void
    {
        Http::fake(['graph.instagram.com/*' => Http::response(['user_id' => '42', 'username' => 'creator'])]);

        $this->assertSame(['id' => '42', 'username' => 'creator'], (new InstagramConnector('t'))->profile());
    }
}
