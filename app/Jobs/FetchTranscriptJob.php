<?php

namespace App\Jobs;

use App\Models\Content;
use App\Models\ContentCopy;
use App\Models\SyncRun;
use App\Platforms\PlatformException;
use App\Platforms\YouTube\TranscriptFetcher;
use Illuminate\Support\Facades\Log;

// One video's transcript, queued from the Library. The result shows in Accounts > Activity like a sync.
class FetchTranscriptJob extends CreatorJob
{
    public int $tries = 1;

    public function __construct(int $creatorId, public int $contentId)
    {
        parent::__construct($creatorId);
    }

    protected function work(): void
    {
        $content = Content::find($this->contentId);
        $copy = $content ? ContentCopy::where('content_id', $content->id)->where('platform', 'youtube')->where('link_mode', '!=', 'target')->first() : null;
        if (! $copy) {
            return;
        }

        $run = SyncRun::create(['connected_account_id' => $copy->connected_account_id, 'kind' => 'transcript', 'status' => 'running', 'started_at' => now()]);
        try {
            app(TranscriptFetcher::class)->fetch($content);
            $run->update(['status' => 'ok', 'fetched' => 1, 'finished_at' => now()]);
        } catch (PlatformException $e) {
            Log::warning("Transcript failed (creator {$this->creatorId}, content {$content->id}): ".$e->getMessage());
            $run->update(['status' => 'failed', 'error' => mb_substr($content->platformId().': '.$e->getMessage(), 0, 300), 'finished_at' => now()]);
        }
    }
}
