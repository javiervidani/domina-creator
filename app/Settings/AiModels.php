<?php

namespace App\Settings;

// The provider's model list holds everything (speech, images, embeddings); the creator only needs text models.
class AiModels
{
    private const NOT_TEXT = ['embedding', 'whisper', 'tts', 'dall-e', 'moderation', 'audio', 'realtime', 'transcribe', 'image', 'vision-preview', 'guard', 'davinci', 'babbage', 'search'];

    /** @param array<int, array<string, mixed>> $data the "data" list of a provider's models answer @return array<string> */
    public static function text(array $data): array
    {
        return collect($data)->pluck('id')->filter(fn ($id) => is_string($id) && $id !== '')
            ->reject(fn ($id) => collect(self::NOT_TEXT)->contains(fn ($bad) => str_contains(strtolower($id), $bad)))
            ->unique()->sort()->values()->all();
    }
}
