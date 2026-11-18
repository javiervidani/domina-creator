<?php

namespace App\Import;

// Pure text helpers for import. Two different questions get two different hashes:
// "did the original change?" (exact apart from line endings) and "is this the same post elsewhere?" (looser).
class CaptionText
{
    public static function clean(string $text): string
    {
        return trim(str_replace(["\r\n", "\r"], "\n", $text));
    }

    public static function changeHash(string $text): string
    {
        return sha1(self::clean($text));
    }

    // Case and spacing differ between networks, so a copy of the same text still matches.
    public static function matchHash(string $text): string
    {
        return sha1(mb_strtolower(preg_replace('/\s+/u', ' ', trim($text)) ?? ''));
    }

    public static function title(string $text, int $max = 80): string
    {
        $line = trim(strtok(self::clean($text), "\n") ?: '');
        if ($line === '') {
            return 'Instagram post';
        }
        if (preg_match('/^(.{1,'.$max.'}?[.!?])(\s|$)/us', $line, $m)) {
            return $m[1];
        }

        return mb_strlen($line) > $max ? rtrim(mb_substr($line, 0, $max - 1)).'…' : $line;
    }
}
