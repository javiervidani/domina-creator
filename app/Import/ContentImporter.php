<?php

namespace App\Import;

use App\Models\ConnectedAccount;
use App\Models\Content;
use App\Models\ContentCopy;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

// Turns one post from a network into content. Returns what happened:
// created | linked | changed | copy_updated | unchanged | skipped.
class ContentImporter
{
    /** @var array<int> contents made by this importer, so the caller can plan their targets */
    public array $created = [];

    public function import(ConnectedAccount $account, array $item): string
    {
        $externalId = (string) ($item['id'] ?? '');
        $text = CaptionText::clean((string) ($item['caption'] ?? ''));

        // Phase 1 is text only: a post with no caption has nothing to import.
        if ($externalId === '' || $text === '') {
            return 'skipped';
        }

        $copy = ContentCopy::where('platform', $account->platform)->where('external_id', $externalId)->first();

        return $copy ? $this->update($copy, $text, $item) : $this->create($account, $item, $externalId, $text);
    }

    private function update(ContentCopy $copy, string $text, array $item = []): string
    {
        $hash = CaptionText::changeHash($text);
        if ($item !== []) {
            $copy->update(array_filter($this->mediaLinks($item))); // never blank a link the sync did not return
        }
        if ($copy->source_hash === $hash) {
            return 'unchanged';
        }

        $old = $copy->source_text;
        $copy->update(['source_text' => $text, 'source_hash' => $hash, 'match_hash' => CaptionText::matchHash($text)]);

        if (! $copy->is_origin) {
            return 'copy_updated';
        }

        $content = $copy->content;
        if (! $content) { // in the Recycle bin: the source text is kept up to date, nothing else is touched
            return 'copy_updated';
        }
        if ($content->master_text === $old) {
            // Never edited here, so following the original is safe.
            $content->update(['master_text' => $text, 'source_changed' => false]);
        } else {
            // Edited here: keep the creator's text and let them choose (Keep mine, Take the new one, Compare).
            $content->update(['source_changed' => true]);
        }

        return 'changed';
    }

    private function create(ConnectedAccount $account, array $item, string $externalId, string $text): string
    {
        $fields = [
            'connected_account_id' => $account->id,
            'platform' => $account->platform,
            'external_id' => $externalId,
            'permalink' => $item['permalink'] ?? null,
            'media_type' => $item['media_type'] ?? null,
            'remote_published_at' => isset($item['timestamp']) ? date('Y-m-d H:i:s', (int) strtotime($item['timestamp'])) : null,
            'source_text' => $text,
        ] + $this->mediaLinks($item) + [
            'source_hash' => CaptionText::changeHash($text),
            'match_hash' => CaptionText::matchHash($text),
        ];

        // The same text already imported from a different account is the same content, not a new one.
        $match = ContentCopy::where('match_hash', $fields['match_hash'])->where('link_mode', '!=', 'target')
            ->where('connected_account_id', '!=', $account->id)->first();

        try {
            if ($match) {
                ContentCopy::create($fields + ['content_id' => $match->content_id, 'is_origin' => false, 'link_mode' => 'auto']);

                return 'linked';
            }

            DB::transaction(function () use ($fields, $text) {
                $content = Content::create(['title' => CaptionText::title($text), 'master_text' => $text]);
                ContentCopy::create($fields + ['content_id' => $content->id, 'is_origin' => true, 'link_mode' => 'origin']);
                $this->created[] = $content->id;
            });

            return 'created';
        } catch (UniqueConstraintViolationException) {
            return 'unchanged'; // two runs met the same new post at once; the other one won
        }
    }

    // Only https links are kept, and only as text: nothing is fetched here.
    private function mediaLinks(array $item): array
    {
        $https = fn ($url) => is_string($url) && str_starts_with($url, 'https://') ? $url : null;

        return ['media_url' => $https($item['media_url'] ?? null), 'thumbnail_url' => $https($item['thumbnail_url'] ?? null)];
    }
}
