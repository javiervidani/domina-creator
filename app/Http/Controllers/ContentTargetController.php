<?php

namespace App\Http\Controllers;

use App\Content\Targets;
use App\Content\VersionRecorder;
use App\Models\Content;
use App\Models\ContentTargetText;
use Illuminate\Http\Request;

// "Same as master" (no row) or the target's own text. The limit is shown in the editor, not enforced here: the
// creator decides, and publishing (step 8) checks it before sending.
class ContentTargetController extends Controller
{
    public function update(Request $request, Content $content, string $target, VersionRecorder $versions)
    {
        abort_unless(Targets::valid($target), 404);

        if ($request->boolean('same')) {
            ContentTargetText::where('content_id', $content->id)->where('target', $target)->delete();
        } else {
            $text = (string) $request->validate(['text' => ['required', 'string']])['text'];
            $row = ContentTargetText::firstOrNew(['content_id' => $content->id, 'target' => $target]);
            $changed = $row->text !== $text;
            $row->text = $text;
            $row->save();
            if ($changed) {
                $versions->record($content, $text, VersionRecorder::EDIT, $target);
            }
        }

        return $request->expectsJson() ? response()->json(['saved' => true]) : back()->with('status', 'Saved.');
    }
}
