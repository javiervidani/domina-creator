<?php

namespace Tests\Feature;

use App\Ai\AiClient;
use App\Ai\AiException;
use App\Ai\Prompts;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

// Fake providers only: no real key and no real post text.
class AiClientTest extends TestCase
{
    public function test_each_provider_family_returns_plain_text(): void
    {
        Http::fake([
            'api.anthropic.com/*' => Http::response(['content' => [['type' => 'text', 'text' => '  Better  ']]]),
            'api.x.ai/*' => Http::response(['choices' => [['message' => ['content' => 'Hi']]]]),
        ]);

        $this->assertSame('Better', AiClient::for('claude', 'k', 'm')->complete('s', 'u'));
        $this->assertSame('Hi', AiClient::for('grok', 'k', 'm')->complete('s', 'u'));
        Http::assertSent(fn ($r) => str_contains($r->url(), 'anthropic') && $r->hasHeader('x-api-key') && $r['system'] === 's');
    }

    public function test_errors_explain_what_to_do_and_never_echo_the_key(): void
    {
        Http::fake(['api.openai.com/*' => Http::response(['error' => ['message' => 'Bad key sk-secret']], 401)]);

        try {
            AiClient::for('openai', 'sk-secret', 'gpt')->complete('s', 'u');
            $this->fail('expected an AiException');
        } catch (AiException $e) {
            $this->assertStringContainsString('Test key', $e->getMessage());
            $this->assertStringNotContainsString('sk-secret', $e->getMessage());
        }
    }

    public function test_shorten_aims_at_the_limit_when_over_it_and_otherwise_a_shorter_text(): void
    {
        $this->assertSame(4096, Prompts::maxLength(5000, 4096));
        $this->assertSame(600, Prompts::maxLength(1000, null));
        $this->assertStringContainsString('at most 600 characters', Prompts::system('shorten', 600));
    }
}
