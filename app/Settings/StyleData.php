<?php

namespace App\Settings;

use App\Models\AiInstruction;
use App\Models\Content;
use App\Models\Setting;

// Everything the Settings > My style tab shows, in one place.
class StyleData
{
    public static function forView(): array
    {
        $job = Setting::text('style_guide_job');
        $draft = Setting::text('style_guide_draft');

        return [
            'marked' => Content::where('my_voice', true)->latest('id')->limit(60)->get(['id', 'title']),
            'markedCount' => Content::where('my_voice', true)->count(),
            'guide' => $draft ?? Setting::text('style_guide') ?? '',
            'isDraft' => $draft !== null,
            'pending' => $job === 'pending',
            'error' => $job && str_starts_with($job, 'error:') ? substr($job, 6) : null,
            'instructions' => AiInstruction::orderByDesc('is_default')->orderBy('name')->get(),
        ];
    }
}
