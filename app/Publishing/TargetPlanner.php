<?php

namespace App\Publishing;

use App\Import\CaptionText;
use App\Models\AccountTarget;
use App\Models\ConnectedAccount;
use App\Models\Content;
use App\Models\ContentCopy;
use Illuminate\Support\Str;

// Makes the draft copy for a target. New targets are review first, so nothing is sent until the creator approves.
class TargetPlanner
{
    public function draftFor(Content $content, ConnectedAccount $target): ContentCopy
    {
        $existing = ContentCopy::where('content_id', $content->id)->where('connected_account_id', $target->id)->first();
        if ($existing) {
            return $existing;
        }

        $text = TargetText::for($content->loadMissing('targetTexts'), $target->platform);

        return ContentCopy::create([
            'content_id' => $content->id, 'connected_account_id' => $target->id, 'platform' => $target->platform,
            'external_id' => 'pending:'.Str::uuid(), 'source_text' => $text, 'source_hash' => CaptionText::changeHash($text),
            'match_hash' => CaptionText::matchHash($text), 'is_origin' => false, 'link_mode' => 'target', 'status' => 'draft',
        ]);
    }

    /**
     * Drafts for freshly imported contents, one per target of the source account.
     *
     * @param  array<int>  $contentIds
     * @return array<int> copy ids whose target is on Auto, to publish right away
     */
    public function planForNew(array $contentIds, ConnectedAccount $source): array
    {
        $auto = [];
        $targets = AccountTarget::where('source_account_id', $source->id)->with('target')->get()
            ->filter(fn ($t) => $t->target && Publisher::supports($t->target->platform));

        foreach (Content::with('targetTexts')->whereIn('id', $contentIds)->get() as $content) {
            foreach ($targets as $link) {
                $copy = $this->draftFor($content, $link->target);
                if ($link->mode === 'auto' && $copy->status === 'draft') {
                    $copy->update(['status' => 'queued']);
                    $auto[] = $copy->id;
                }
            }
        }

        return $auto;
    }
}
