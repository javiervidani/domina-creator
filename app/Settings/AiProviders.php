<?php

namespace App\Settings;

// The providers a creator can add a key for, and how a key is checked. Testing only lists the provider's models,
// which proves the key works without generating any text or costing anything.
class AiProviders
{
    public const ALL = [
        'claude' => ['label' => 'Claude (Anthropic)', 'model' => 'claude-sonnet-5-5', 'url' => 'https://api.anthropic.com/v1/models', 'chat' => 'https://api.anthropic.com/v1/messages'],
        'openai' => ['label' => 'OpenAI', 'model' => 'gpt-4o-mini', 'url' => 'https://api.openai.com/v1/models', 'chat' => 'https://api.openai.com/v1/chat/completions'],
        'grok' => ['label' => 'Grok (xAI)', 'model' => 'grok-2', 'url' => 'https://api.x.ai/v1/models', 'chat' => 'https://api.x.ai/v1/chat/completions'],
        'groq' => ['label' => 'Groq', 'model' => 'llama-3.3-70b-versatile', 'url' => 'https://api.groq.com/openai/v1/models', 'chat' => 'https://api.groq.com/openai/v1/chat/completions'],
    ];

    public static function valid(string $provider): bool
    {
        return isset(self::ALL[$provider]);
    }

    /** @return array<string, string> request headers carrying the key; they never reach the logs */
    public static function headers(string $provider, string $key): array
    {
        return $provider === 'claude'
            ? ['x-api-key' => $key, 'anthropic-version' => '2023-06-01']
            : ['Authorization' => 'Bearer '.$key];
    }
}
