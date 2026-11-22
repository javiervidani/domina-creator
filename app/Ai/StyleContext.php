<?php

namespace App\Ai;

use App\Models\Content;
use App\Models\Setting;

// What "In my style" adds to a request: the creator's own style guide and a few of their own marked posts as examples.
class StyleContext
{
    public const EXAMPLES = 4;
    public const EXAMPLE_CHARS = 1500;
    public const GUIDE_POSTS = 30;
    public const GUIDE_CHARS = 24000; // total post text sent when drafting the guide

    /** @return array{guide: ?string, examples: array<string>} */
    public static function for(int $excludeContentId): array
    {
        $guide = Setting::text('style_guide');
        $examples = Content::where('my_voice', true)->where('id', '!=', $excludeContentId)->inRandomOrder()->limit(self::EXAMPLES)->pluck('master_text')
            ->map(fn ($t) => mb_substr($t, 0, self::EXAMPLE_CHARS))->all();

        return ['guide' => $guide !== null && trim($guide) !== '' ? $guide : null, 'examples' => $examples];
    }

    public static function available(): bool
    {
        return trim((string) Setting::text('style_guide')) !== '' || Content::where('my_voice', true)->exists();
    }

    public static function note(array $style): string
    {
        $parts = array_filter([$style['guide'] ? 'your style guide' : null, $style['examples'] ? count($style['examples']).' of your posts' : null]);

        return implode(' + ', $parts);
    }

    /** @return array<string> the marked posts used to draft the guide, newest first, trimmed to a total budget */
    public static function guidePosts(): array
    {
        $used = 0;
        $out = [];
        foreach (Content::where('my_voice', true)->latest('id')->limit(self::GUIDE_POSTS)->pluck('master_text') as $text) {
            $text = mb_substr($text, 0, self::EXAMPLE_CHARS * 2);
            if ($used + mb_strlen($text) > self::GUIDE_CHARS) {
                break;
            }
            $used += mb_strlen($text);
            $out[] = $text;
        }

        return $out;
    }
}
