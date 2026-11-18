<?php

namespace App\Content;

use App\Models\Content;
use App\Models\ContentVersion;

// Writes History entries. Plain edits (autosave fires often) are merged into the last entry if it is under five
// minutes old; deliberate actions (Took the new version, Rebuilt from sources) always get their own entry.
class VersionRecorder
{
    public const EDIT = 'Edited';

    public function record(Content $content, string $text, string $note = self::EDIT, ?string $target = null): ContentVersion
    {
        if ($note === self::EDIT) {
            $last = ContentVersion::where('content_id', $content->id)->where('target', $target)->latest('id')->first();
            if ($last && $last->note === self::EDIT && $last->updated_at->gt(now()->subMinutes(5))) {
                $last->update(['text' => $text, 'title' => $target ? null : $content->title]);

                return $last;
            }
        }

        return ContentVersion::create(['content_id' => $content->id, 'target' => $target, 'note' => $note,
            'title' => $target ? null : $content->title, 'text' => $text]);
    }
}
