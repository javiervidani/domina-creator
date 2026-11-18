<?php

namespace App\Http\Controllers;

use App\Jobs\PublishCopyJob;
use App\Models\ConnectedAccount;
use App\Models\Content;
use App\Models\ContentCopy;
use App\Platforms\Telegram\TelegramConnector;
use App\Publishing\Publisher;
use App\Publishing\TargetPlanner;
use App\Publishing\TargetText;
use Illuminate\Http\Request;

// Everything that sends a copy out is a button the creator presses. Nothing here runs by itself.
class PublishingController extends Controller
{
    // "Create draft": a review-first copy for an account that has none yet. Nothing is sent.
    public function draft(Content $content, ConnectedAccount $account, TargetPlanner $planner)
    {
        abort_unless(Publisher::supports($account->platform), 404);
        $planner->draftFor($content, $account);

        return back()->with('status', 'Draft created for '.ucfirst($account->platform).'. Approve it to publish.');
    }

    // Approve and publish, Re-publish and Retry are the same action: send what the target text says now.
    public function publish(Request $request, Content $content, ContentCopy $copy)
    {
        abort_unless($copy->content_id === $content->id && $copy->link_mode === 'target', 404);

        $text = TargetText::for($content->loadMissing('targetTexts'), $copy->platform);
        if (mb_strlen($text) > TelegramConnector::LIMIT) {
            return back()->with('error', 'The text is '.mb_strlen($text).' characters; Telegram allows '.TelegramConnector::LIMIT.'. Shorten it in the Telegram tab first.');
        }

        $copy->update(['status' => 'queued', 'last_error' => null]);
        // After the response, outside the creator schema: the queue table is in public.
        PublishCopyJob::dispatch($request->attributes->get('creator_id'), $copy->id)->afterResponse();

        return back()->with('status', 'Sending to '.ucfirst($copy->platform).'. Refresh in a moment to see the result.');
    }
}
