<?php

namespace App\Library;

use App\Models\Content;
use Illuminate\Database\Eloquent\Builder;

// Filters for the Library list. Takes plain values so it is the same for the page and for tests.
class LibraryQuery
{
    public const STATUSES = ['imported', 'draft'];

    public static function build(array $f): Builder
    {
        $q = Content::query()->with(['copies', 'tags']);

        if (($f['q'] ?? '') !== '') {
            $term = '%'.addcslashes($f['q'], '%_\\').'%';
            $q->where(fn ($w) => $w->where('title', 'ilike', $term)->orWhere('master_text', 'ilike', $term));
        }
        if (! empty($f['account'])) {
            $q->whereHas('copies', fn ($c) => $c->where('connected_account_id', (int) $f['account']));
        }
        if (in_array($f['status'] ?? '', self::STATUSES, true)) {
            $q->where('status', $f['status']);
        }
        // "Needs review" is the same list as "source changed" until publishing (step 8) adds drafts to approve.
        if (! empty($f['changed']) || ($f['view'] ?? '') === 'review') {
            $q->where('source_changed', true);
        }

        // Newest post first. Imports run newest first, so ids alone would show the oldest on top.
        return $q->orderByRaw('coalesce((select c.remote_published_at from content_copies c where c.content_id = contents.id and c.is_origin limit 1), contents.created_at) desc')
            ->orderByDesc('id');
    }
}
