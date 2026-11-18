<?php

namespace App\Http\Controllers;

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

    /** @return array<int> */
    private function ids(Request $request): array
    {
        return array_values(array_unique(array_map('intval', (array) $request->input('ids', []))));
    }
}
