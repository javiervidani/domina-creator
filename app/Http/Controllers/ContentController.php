<?php

namespace App\Http\Controllers;

use App\Content\TextDiff;
use App\Content\VersionRecorder;
use App\Models\ConnectedAccount;
use App\Models\AiKey;
use App\Models\AiSuggestion;
use App\Models\Content;
use App\Publishing\Publisher;
use App\Publishing\TargetText;
use Illuminate\Http\Request;

// The Content editor page and the master text/title save. Autosave posts here as JSON.
class ContentController extends Controller
{
    public function show(Content $content)
    {
        $content->load(['parts', 'copies', 'tags', 'targetTexts', 'versions']);
        $origin = $content->copies->firstWhere('is_origin', true);

        // Per target copy: does the text now differ from what was last sent? Shown next to Re-publish.
        $differs = $content->copies->where('link_mode', 'target')->mapWithKeys(
            fn ($c) => [$c->id => Publisher::differs($c, TargetText::for($content, $c->platform))]);
        $sendable = ConnectedAccount::whereIn('platform', Publisher::SUPPORTED)
            ->whereNotIn('id', $content->copies->pluck('connected_account_id')->filter())->get(['id', 'platform', 'handle']);

        return view('content', [
            'media' => $origin,
            'differs' => $differs, 'sendable' => $sendable,
            'content' => $content,
            'aiReady' => AiKey::where('enabled', true)->exists(),
            'suggestions' => AiSuggestion::where('content_id', $content->id)->whereIn('status', ['pending', 'offered', 'failed'])->latest('id')->get()->unique('target')->keyBy('target'),
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
