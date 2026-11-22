<?php

namespace App\Http\Controllers;

use App\Content\VersionRecorder;
use App\Models\Content;
use App\Models\ContentTranscript;
use App\Platforms\PlatformException;
use App\Platforms\YouTube\TranscriptFetcher;
use Illuminate\Http\Request;

// The creator's own YouTube captions as editable text. Kept in its own table; the master text changes only on a click.
class TranscriptsController extends Controller
{
    public function fetch(Content $content, TranscriptFetcher $fetcher)
    {
        try {
            $fetcher->fetch($content);
        } catch (PlatformException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Transcript added below. Your text is unchanged until you use it.');
    }

    // mode "replace" swaps the master text, "append" adds the transcript after it. Either way the old text goes to History first.
    public function use(Request $request, Content $content, ContentTranscript $transcript, VersionRecorder $versions)
    {
        abort_unless($transcript->content_id === $content->id, 404);
        $append = $request->input('mode') === 'append';

        $versions->record($content, $content->master_text, 'Before the transcript');
        $content->update(['master_text' => $append ? rtrim($content->master_text)."\n\n".$transcript->text : $transcript->text]);
        $versions->record($content, $content->master_text, $append ? 'Transcript added below the text' : 'Replaced with the transcript');

        return back()->with('status', 'Done. Your earlier text is in History.');
    }

    public function destroy(Content $content, ContentTranscript $transcript)
    {
        abort_unless($transcript->content_id === $content->id, 404);
        $transcript->delete();

        return back()->with('status', 'Transcript removed.');
    }
}
