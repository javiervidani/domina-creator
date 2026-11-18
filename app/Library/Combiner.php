<?php

namespace App\Library;

use App\Models\Content;
use App\Models\ContentPart;
use Illuminate\Support\Facades\DB;

// Several contents become the parts of one new draft. The selected contents stay as they are.
class Combiner
{
    /** @param array<int> $ids in the order the creator wants them */
    public function combine(array $ids): Content
    {
        $sources = Content::whereIn('id', $ids)->get()->keyBy('id');
        $ordered = collect($ids)->map(fn ($id) => $sources->get((int) $id))->filter();

        return DB::transaction(function () use ($ordered) {
            $content = Content::create([
                'title' => 'Combined: '.$ordered->first()->title,
                'master_text' => $ordered->pluck('master_text')->implode("\n\n"),
                'status' => 'draft',
            ]);

            foreach ($ordered->values() as $i => $source) {
                ContentPart::create(['content_id' => $content->id, 'position' => $i + 1, 'source_content_id' => $source->id,
                    'label' => $source->platformId(), 'text' => $source->master_text]);
            }

            return $content;
        });
    }
}
