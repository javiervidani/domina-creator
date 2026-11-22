<?php

namespace App\Http\Controllers;

use App\Jobs\FetchTranscriptJob;
use App\Library\Combiner;
use App\Models\Content;
use App\Models\Tag;
use Illuminate\Http\Request;

class LibraryBulkController extends Controller
{
    public function combine(Request $request, Combiner $combiner)
    {
        $ids = $this->ids($request);
        if (count($ids) < 2) {
            return back()->with('error', 'Select at least two items to combine.');
        }

        return redirect()->route('content.show', $combiner->combine($ids));
    }

    public function tag(Request $request)
    {
        $ids = $this->ids($request);
        $name = mb_substr(trim((string) $request->input('tag')), 0, 40);
        if (! $ids || $name === '') {
            return back()->with('error', 'Select items and type a tag.');
        }

        $tag = Tag::firstOrCreate(['name' => $name]);
        foreach (Content::whereIn('id', $ids)->get() as $content) {
            $content->tags()->syncWithoutDetaching([$tag->id]);
        }

        return back()->with('status', 'Tagged '.count($ids).' item'.(count($ids) > 1 ? 's' : '').' with "'.$name.'".');
    }

    // YouTube posts only, one queued job each (YouTube allows roughly 40 caption downloads a day, so a click is capped).
    public function transcripts(Request $request)
    {
        $ids = $request->filled('one') ? [(int) $request->input('one')] : $this->ids($request);
        $contents = Content::whereIn('id', $ids)->whereHas('copies', fn ($c) => $c->where('platform', 'youtube')->where('link_mode', '!=', 'target'))
            ->whereDoesntHave('transcripts')->limit(30)->pluck('id');
        if ($contents->isEmpty()) {
            return back()->with('error', 'Select YouTube posts that have no transcript yet.');
        }
        foreach ($contents as $id) {
            FetchTranscriptJob::dispatch($request->attributes->get('creator_id'), $id)->afterResponse();
        }

        return back()->with('status', 'Getting '.$contents->count().' transcript'.($contents->count() > 1 ? 's' : '').'. Reload in a moment; the result is also in Accounts, Activity.');
    }

    /** @return array<int> */
    private function ids(Request $request): array
    {
        return array_values(array_unique(array_map('intval', (array) $request->input('ids', []))));
    }
}
