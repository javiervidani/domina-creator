<?php

namespace App\Http\Controllers;

use App\Content\VersionRecorder;
use App\Models\ConnectedAccount;
use App\Models\Content;
use App\Models\ContentCopy;
use App\Models\ContentTranscript;
use App\Platforms\PlatformException;
use App\Platforms\YouTube\Srt;
use App\Platforms\YouTube\YouTubeConnector;
use Illuminate\Http\Request;

// The creator's own YouTube captions as editable text. Kept in its own table; the master text changes only on a click.
class TranscriptsController extends Controller
{
    public function fetch(Content $content)
    {
        $copy = ContentCopy::where('content_id', $content->id)->where('platform', 'youtube')->where('link_mode', '!=', 'target')->first();
        $account = $copy ? ConnectedAccount::with('token')->find($copy->connected_account_id) : null;
        if (! $copy || ! $account?->token) {
            return back()->with('error', 'This post has no connected YouTube video.');
        }

        try {
            $connector = YouTubeConnector::forAccount($account);
            $track = Srt::choose($connector->captionTracks($copy->external_id));
            if (! $track) {
                return back()->with('error', 'This video has no captions to download. Add captions in YouTube Studio first (speech to text is planned, not built yet).');
            }
            $text = Srt::toText($connector->downloadCaption($track['id']));
        } catch (PlatformException $e) {
            if ($e->needsReconnect) {
                $account->update(['status' => 'reconnect', 'last_error' => $e->getMessage()]);
            }

            return back()->with('error', $e->getMessage());
        }
        if ($text === '') {
            return back()->with('error', 'The caption track was empty.');
        }

        ContentTranscript::updateOrCreate(['content_copy_id' => $copy->id], ['content_id' => $content->id,
            'language' => $track['language'], 'kind' => $track['kind'] === 'asr' ? 'asr' : 'standard', 'text' => $text]);

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
