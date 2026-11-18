<?php

namespace App\Http\Controllers;

use App\Content\VersionRecorder;
use App\Models\Content;
use App\Models\ContentPart;
use Illuminate\Http\Request;

// The Sources list. Adding a source appends its text to the master text; moving and removing only change the list,
// and "Rebuild text from sources" is the one action that replaces the master text from it.
class ContentPartsController extends Controller
{
    public function add(Request $request, Content $content, VersionRecorder $versions)
    {
        $kind = $request->validate(['kind' => ['required', 'in:library,paste,transcript']])['kind'];

        if ($kind === 'library') {
            $source = Content::findOrFail((int) $request->input('source_id'));
            [$label, $text] = [$source->platformId(), $source->master_text];
        } else {
            $text = trim((string) $request->validate(['text' => ['required', 'string']])['text']);
            $label = $kind === 'paste' ? 'Pasted text' : 'Video transcript';
        }

        // A single imported post has no list yet: its current text becomes the first source.
        if (! ContentPart::where('content_id', $content->id)->exists()) {
            $this->make($content, 1, 'Original', $content->master_text);
        }
        $this->make($content, (int) ContentPart::where('content_id', $content->id)->max('position') + 1, $label, $text, $kind === 'library' ? $source->id : null);

        $content->update(['master_text' => rtrim($content->master_text)."\n\n".$text]);
        $versions->record($content, $content->master_text, 'Added source: '.$label);

        return back()->with('status', 'Source added.');
    }

    public function move(Request $request, Content $content, ContentPart $part)
    {
        abort_unless($part->content_id === $content->id, 404);
        $step = $request->input('dir') === 'up' ? -1 : 1;
        $parts = $content->parts()->get()->values();
        $i = $parts->search(fn ($p) => $p->id === $part->id);
        $swap = $parts->get($i + $step);

        if ($swap) {
            [$a, $b] = [$part->position, $swap->position];
            $part->update(['position' => $b]);
            $swap->update(['position' => $a]);
        }

        return back();
    }

    public function remove(Content $content, ContentPart $part)
    {
        abort_unless($part->content_id === $content->id, 404);
        $part->delete();

        return back()->with('status', 'Source removed from the list. The text is unchanged.');
    }

    public function rebuild(Content $content, VersionRecorder $versions)
    {
        $parts = $content->parts()->get();
        if ($parts->isNotEmpty()) {
            $content->update(['master_text' => $parts->pluck('text')->implode("\n\n")]);
            $versions->record($content, $content->master_text, 'Rebuilt from sources');
        }

        return back()->with('status', 'Text rebuilt from the sources.');
    }

    private function make(Content $content, int $position, string $label, string $text, ?int $sourceId = null): void
    {
        ContentPart::create(['content_id' => $content->id, 'position' => $position, 'source_content_id' => $sourceId,
            'label' => $label, 'text' => $text]);
    }
}
