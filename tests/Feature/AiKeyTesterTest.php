<?php

namespace Tests\Feature;

use App\Settings\AiKeyTester;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

// Fake HTTP only: no provider is called and no key is real.
class AiKeyTesterTest extends TestCase
{
    public function test_each_provider_is_checked_with_the_right_header_and_url(): void
    {
        Http::fake(['*' => Http::response(['data' => []])]);
        $t = new AiKeyTester;

        $this->assertTrue($t->test('claude', 'k1')['ok']);
        $this->assertTrue($t->test('openai', 'k2')['ok']);

        Http::assertSent(fn ($r) => str_starts_with($r->url(), 'https://api.anthropic.com/v1/models') && $r->header('x-api-key') === ['k1']);
        Http::assertSent(fn ($r) => str_starts_with($r->url(), 'https://api.openai.com/v1/models') && $r->header('Authorization') === ['Bearer k2']);
    }

    public function test_a_rejected_key_reports_the_provider_reason_without_the_key(): void
    {
        Http::fake(['*' => Http::response(['error' => ['message' => 'Incorrect API key provided: secret-key-123']], 401)]);

        $result = (new AiKeyTester)->test('openai', 'secret-key-123');

        $this->assertFalse($result['ok']);
        $this->assertStringNotContainsString('secret-key-123', $result['error']);
    }

    public function test_unknown_provider_is_refused(): void
    {
        Http::fake();

        $this->assertFalse((new AiKeyTester)->test('nope', 'k')['ok']);
        Http::assertNothingSent();
    }
}
