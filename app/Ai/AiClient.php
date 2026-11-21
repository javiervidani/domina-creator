<?php

namespace App\Ai;

use App\Settings\AiProviders;
use Illuminate\Http\Client\Response;
use Throwable;

// One call: a system instruction and the post text in, plain text out. Subclasses only know how their provider's
// request and answer look; errors are explained here once.
abstract class AiClient
{
    public function __construct(protected string $key, protected string $model)
    {
    }

    public static function for(string $provider, string $key, string $model): self
    {
        return $provider === 'claude' ? new AnthropicClient($key, $model) : new ChatClient($key, $model, $provider);
    }

    abstract protected function send(string $system, string $user): Response;

    abstract protected function extract(Response $response): ?string;

    /** @throws AiException */
    public function complete(string $system, string $user): string
    {
        try {
            $response = $this->send($system, $user);
        } catch (Throwable) {
            throw new AiException('Could not reach the AI provider. Try again in a moment.');
        }
        if (! $response->successful()) {
            throw new AiException($this->explain($response));
        }
        $text = trim((string) $this->extract($response));
        if ($text === '') {
            throw new AiException('The AI provider returned no text. Try again.');
        }

        return $text;
    }

    private function explain(Response $response): string
    {
        $said = $response->json('error.message') ?? $response->json('error');
        $said = is_string($said) ? mb_substr(str_replace($this->key, '[key]', $said), 0, 160) : 'status '.$response->status();
        $hint = match (true) {
            in_array($response->status(), [401, 403], true) => 'The provider rejected the key. Check it in Settings, AI keys, and press Test key.',
            $response->status() === 404 => 'The provider does not know the model "'.$this->model.'". Change the default model in Settings, AI keys.',
            $response->status() === 429 => 'The provider says too many requests, or the quota or credit is used up. Try later or check your plan.',
            $response->status() >= 500 => 'The AI provider had a problem on its side. Try again in a moment.',
            default => 'The AI provider did not accept the request.',
        };

        return $hint.' (Provider said: '.$said.')';
    }

    protected function headers(string $provider): array
    {
        return AiProviders::headers($provider, $this->key);
    }
}
