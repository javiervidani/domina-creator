<?php

namespace App\Http\Controllers;

use App\Content\TextDiff;
use App\Content\VersionRecorder;
use App\Models\Content;
use Illuminate\Http\Request;

// The Content editor page and the master text/title save. Autosave posts here as JSON.
class ContentController extends Controller
{
    public function show(Content $content)
    {
        $content->load(['parts', 'copies', 'tags', 'targetTexts', 'versions']);
        $origin = $content->copies->firstWhere('is_origin', true);

        return view('content', [
            'content' => $content,
            'targetTexts' => $content->targetTexts->pluck('text', 'target'),
            'diff' => $content->source_changed && $origin ? TextDiff::sides($content->master_text, $origin->source_text) : null,
            'others' => Content::where('id', '!=', $content->id)->orderByDesc('id')->limit(100)->get(['id', 'title']),
        ]);
    }

    // Only the master text and title change. The imported originals (content_copies) are never touched.
    public function update(Request $request, Content $content, VersionRecorder $versions)
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:255'], 'master_text' => ['required', 'string']]);
        $changed = $content->master_text !== $data['master_text'] || $content->title !== $data['title'];
        $content->update($data);

        if ($changed) {
            $versions->record($content, $data['master_text']);
        }

        return $request->expectsJson() ? response()->json(['saved' => true]) : back()->with('status', 'Saved.');
    }
}
