<?php

namespace App\Jobs;

use App\Ai\AiClient;
use App\Ai\AiException;
use App\Ai\Prompts;
use App\Ai\StyleContext;
use App\Models\AiKey;
use App\Models\Setting;
use App\Settings\AiProviders;
use Illuminate\Support\Facades\Log;

// Drafts the creator's style guide from their marked posts. The draft waits in Settings; nothing becomes "the guide" until they save it.
class StyleGuideJob extends CreatorJob
{
    public int $tries = 1;

    public int $timeout = 150;

    protected function work(): void
    {
        $key = AiKey::usable();
        $posts = StyleContext::guidePosts();
        if (! $key || ! $posts) {
            Setting::putText('style_guide_job', 'error:Add an AI key and mark some of your posts first.');

            return;
        }

        try {
            $model = $key->default_model ?: AiProviders::ALL[$key->provider]['model'];
            $guide = AiClient::for($key->provider, $key->api_key, $model)->complete(Prompts::styleGuide(), implode("\n-----\n", $posts));
            Setting::putText('style_guide_draft', $guide);
            Setting::putText('style_guide_job', null);
        } catch (AiException $e) {
            Log::warning("Style guide failed (creator {$this->creatorId}, provider {$key->provider}): ".$e->getMessage());
            Setting::putText('style_guide_job', 'error:'.mb_substr($e->getMessage(), 0, 300));
        }
    }
}
