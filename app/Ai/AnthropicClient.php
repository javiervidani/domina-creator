<?php

namespace App\Ai;

use App\Settings\AiProviders;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class AnthropicClient extends AiClient
{
    protected function send(string $system, string $user): Response
    {
        return Http::timeout(90)->withHeaders($this->headers('claude'))->post(AiProviders::ALL['claude']['chat'], [
            'model' => $this->model, 'max_tokens' => 2048, 'system' => $system,
            'messages' => [['role' => 'user', 'content' => $user]],
        ]);
    }

    protected function extract(Response $response): ?string
    {
        return $response->json('content.0.text');
    }
}
