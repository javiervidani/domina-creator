<?php

namespace App\Http\Controllers;

use App\Content\VersionRecorder;
use App\Models\Content;

// The three answers to "the original changed": Keep mine, Take the new version (Compare is shown on the page).
// The text is never replaced unless the creator takes the new version.
class ContentSourceController extends Controller
{
    public function keep(Content $content, VersionRecorder $versions)
    {
        $content->update(['source_changed' => false]);
        $versions->record($content, $content->master_text, 'Kept my version');

        return back()->with('status', 'Keeping your version.');
    }

    public function take(Content $content, VersionRecorder $versions)
    {
        $origin = $content->copies()->where('is_origin', true)->first();
        abort_unless($origin, 404);

        // The old text stays in History, so taking the new version can be undone by hand.
        $versions->record($content, $content->master_text, 'Before taking the new version');
        $content->update(['master_text' => $origin->source_text, 'source_changed' => false]);
        $versions->record($content, $content->master_text, 'Took the new version');

        return back()->with('status', 'Took the new version. Your earlier text is in History.');
    }
}
