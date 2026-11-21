<?php

namespace App\Ai;

use App\Settings\AiProviders;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

// OpenAI, Grok and Groq share the same "chat completions" request.
class ChatClient extends AiClient
{
    public function __construct(string $key, string $model, private string $provider)
    {
        parent::__construct($key, $model);
    }

    protected function send(string $system, string $user): Response
    {
        return Http::timeout(90)->withHeaders($this->headers($this->provider))->post(AiProviders::ALL[$this->provider]['chat'], [
            'model' => $this->model,
            'messages' => [['role' => 'system', 'content' => $system], ['role' => 'user', 'content' => $user]],
        ]);
    }

    protected function extract(Response $response): ?string
    {
        return $response->json('choices.0.message.content');
    }
}
