<?php

namespace Tests\Feature;

use App\Platforms\PlatformException;
use App\Platforms\Telegram\TelegramConnector;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

// Fake HTTP only: no real bot, no database.
class TelegramConnectorTest extends TestCase
{
    public function test_send_returns_the_message_id(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 77]])]);

        $this->assertSame('77', (new TelegramConnector('tok', '@chan'))->send('שלום'));
        Http::assertSent(fn ($r) => str_contains($r->url(), '/sendMessage') && $r['text'] === 'שלום' && $r['chat_id'] === '@chan');
    }

    public function test_edit_updates_in_place_and_ignores_not_modified(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => false, 'error_code' => 400, 'description' => 'Bad Request: message is not modified'], 400)]);

        (new TelegramConnector('tok', '@chan'))->edit('77', 'same text');

        Http::assertSent(fn ($r) => str_contains($r->url(), '/editMessageText') && $r['message_id'] === 77);
        $this->assertTrue(true); // no exception
    }

    public function test_too_long_text_is_refused_before_calling_telegram(): void
    {
        Http::fake();

        $this->expectException(PlatformException::class);
        try {
            (new TelegramConnector('tok', '@chan'))->send(str_repeat('a', 4097));
        } finally {
            Http::assertNothingSent();
        }
    }

    public function test_rate_limit_is_retryable_and_bad_token_asks_for_reconnect_without_leaking_the_token(): void
    {
        Http::fake(['api.telegram.org/*' => Http::sequence()
            ->push(['ok' => false, 'error_code' => 429, 'description' => 'Too Many Requests'], 429)
            ->push(['ok' => false, 'error_code' => 401, 'description' => 'Unauthorized'], 401)]);
        $c = new TelegramConnector('secret-bot-token', '@chan');

        try {
            $c->send('x');
            $this->fail('expected');
        } catch (PlatformException $e) {
            $this->assertTrue($e->retryable);
        }
        try {
            $c->send('x');
            $this->fail('expected');
        } catch (PlatformException $e) {
            $this->assertTrue($e->needsReconnect);
            $this->assertStringNotContainsString('secret-bot-token', $e->getMessage());
        }
    }
}
