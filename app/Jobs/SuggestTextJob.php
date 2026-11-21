<?php

namespace App\Jobs;

use App\Ai\AiClient;
use App\Ai\AiException;
use App\Ai\Prompts;
use App\Content\Targets;
use App\Models\AiKey;
use App\Models\AiSuggestion;
use App\Models\Content;
use App\Publishing\TargetText;
use App\Settings\AiProviders;
use Illuminate\Support\Facades\Log;

// Asks the creator's own AI key for one suggestion. Runs queued so a slow provider never blocks the page.
class SuggestTextJob extends CreatorJob
{
    public int $tries = 1;

    public int $timeout = 120;

    public function __construct(int $creatorId, public int $suggestionId)
    {
        parent::__construct($creatorId);
    }

    protected function work(): void
    {
        $suggestion = AiSuggestion::find($this->suggestionId);
        $content = $suggestion ? Content::find($suggestion->content_id) : null;
        $key = $suggestion ? AiKey::where('provider', $suggestion->provider)->first() : null;
        if (! $suggestion || $suggestion->status !== 'pending') {
            return;
        }
        if (! $content || ! $key || ! $key->enabled) {
            $suggestion->update(['status' => 'failed', 'error' => 'The post or the AI key is no longer available.']);

            return;
        }

        $text = $suggestion->target === 'master' ? $content->master_text : TargetText::for($content->loadMissing('targetTexts'), $suggestion->target);
        $max = Prompts::maxLength(mb_strlen($text), Targets::LIMITS[$suggestion->target] ?? null);

        try {
            $out = AiClient::for($suggestion->provider, $key->api_key, $suggestion->model)->complete(Prompts::system($suggestion->action, $max), $text);
            $suggestion->update(['status' => 'offered', 'offered_text' => $out]);
        } catch (AiException $e) {
            // Provider and creator only: the log never holds the post text or the key.
            Log::warning("AI suggestion failed (creator {$this->creatorId}, provider {$suggestion->provider}): ".$e->getMessage());
            $suggestion->update(['status' => 'failed', 'error' => mb_substr($e->getMessage(), 0, 300)]);
        }
    }
}
