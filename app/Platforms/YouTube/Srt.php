<?php

namespace App\Platforms\YouTube;

// Turns a caption file (SRT) into plain readable text: no numbers, no timestamps, a new paragraph where the speaker pauses.
class Srt
{
    private const PAUSE = 2.5; // seconds of silence that start a new paragraph

    public static function toText(string $srt): string
    {
        $srt = str_replace(["\r\n", "\r"], "\n", trim($srt));
        $paragraphs = [];
        $current = [];
        $lastEnd = null;

        foreach (preg_split("/\n{2,}/", $srt) as $cue) {
            $lines = array_values(array_filter(array_map('trim', explode("\n", $cue)), 'strlen'));
            if (isset($lines[0]) && ctype_digit($lines[0])) {
                array_shift($lines);
            }
            if (! isset($lines[0]) || ! preg_match('/(\d+):(\d+):(\d+)[,.](\d+)\s*-->\s*(\d+):(\d+):(\d+)[,.](\d+)/', $lines[0], $m)) {
                continue;
            }
            array_shift($lines);
            [$start, $end] = [$m[1] * 3600 + $m[2] * 60 + $m[3] + $m[4] / 1000, $m[5] * 3600 + $m[6] * 60 + $m[7] + $m[8] / 1000];
            $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags(implode(' ', $lines)), ENT_QUOTES | ENT_HTML5)));
            if ($text === '') {
                continue;
            }
            if ($lastEnd !== null && $start - $lastEnd >= self::PAUSE && $current) {
                $paragraphs[] = implode(' ', $current);
                $current = [];
            }
            $current[] = $text;
            $lastEnd = $end;
        }
        if ($current) {
            $paragraphs[] = implode(' ', $current);
        }

        return implode("\n\n", $paragraphs);
    }

    /** Standard (uploaded) tracks first, then automatic ones; Hebrew before the rest. */
    public static function choose(array $tracks): ?array
    {
        usort($tracks, fn ($a, $b) => [$a['kind'] === 'asr', ! in_array($a['language'], ['he', 'iw'], true)] <=> [$b['kind'] === 'asr', ! in_array($b['language'], ['he', 'iw'], true)]);

        return $tracks[0] ?? null;
    }
}
