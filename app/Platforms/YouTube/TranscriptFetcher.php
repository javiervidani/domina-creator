<?php

namespace App\Platforms\YouTube;

use App\Models\ConnectedAccount;
use App\Models\Content;
use App\Models\ContentCopy;
use App\Models\ContentTranscript;
use App\Platforms\PlatformException;

// Pulls the creator's own caption track for one video into content_transcripts. Used by the editor button, the Library
// buttons and the queued job, so the rules (which track, how it becomes text) live in one place.
class TranscriptFetcher
{
    /** @throws PlatformException with a message written for the creator */
    public function fetch(Content $content): ContentTranscript
    {
        $copy = ContentCopy::where('content_id', $content->id)->where('platform', 'youtube')->where('link_mode', '!=', 'target')->first();
        $account = $copy ? ConnectedAccount::with('token')->find($copy->connected_account_id) : null;
        if (! $copy || ! $account?->token) {
            throw new PlatformException('This post has no connected YouTube video.');
        }

        try {
            $connector = YouTubeConnector::forAccount($account);
            $track = Srt::choose($connector->captionTracks($copy->external_id));
            if (! $track) {
                throw new PlatformException('This video has no captions to download. Add captions in YouTube Studio first (speech to text is planned, not built yet).');
            }
            $text = Srt::toText($connector->downloadCaption($track['id']));
        } catch (PlatformException $e) {
            if ($e->needsReconnect) {
                $account->update(['status' => 'reconnect', 'last_error' => $e->getMessage()]);
            }
            throw $e;
        }
        if ($text === '') {
            throw new PlatformException('The caption track was empty.');
        }

        return ContentTranscript::updateOrCreate(['content_copy_id' => $copy->id], ['content_id' => $content->id,
            'language' => $track['language'], 'kind' => $track['kind'] === 'asr' ? 'asr' : 'standard', 'text' => $text]);
    }
}
