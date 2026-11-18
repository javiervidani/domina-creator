<?php

namespace App\Content;

// Word-level compare for the "source changed" banner. Returns two escaped HTML strings: yours with the words that
// are gone marked, and the new original with the added words marked. Our own small version of what php-diff would do.
class TextDiff
{
    private const MAX_TOKENS = 3000;

    /** @return array{mine: string, theirs: string} */
    public static function sides(string $mine, string $theirs): array
    {
        $a = self::tokens($mine);
        $b = self::tokens($theirs);

        // Too long to compare word by word: show both whole rather than stall the page.
        if (count($a) > self::MAX_TOKENS || count($b) > self::MAX_TOKENS) {
            return ['mine' => e($mine), 'theirs' => e($theirs)];
        }

        $n = count($a);
        $m = count($b);
        $lcs = array_fill(0, $n + 1, array_fill(0, $m + 1, 0));
        for ($i = $n - 1; $i >= 0; $i--) {
            for ($j = $m - 1; $j >= 0; $j--) {
                $lcs[$i][$j] = $a[$i] === $b[$j] ? $lcs[$i + 1][$j + 1] + 1 : max($lcs[$i + 1][$j], $lcs[$i][$j + 1]);
            }
        }

        $left = $right = '';
        $i = $j = 0;
        while ($i < $n && $j < $m) {
            if ($a[$i] === $b[$j]) {
                $left .= e($a[$i]);
                $right .= e($b[$j]);
                $i++;
                $j++;
            } elseif ($lcs[$i + 1][$j] >= $lcs[$i][$j + 1]) {
                $left .= self::mark($a[$i++]);
            } else {
                $right .= self::mark($b[$j++]);
            }
        }
        while ($i < $n) {
            $left .= self::mark($a[$i++]);
        }
        while ($j < $m) {
            $right .= self::mark($b[$j++]);
        }

        return ['mine' => $left, 'theirs' => $right];
    }

    /** @return array<string> words and the spaces between them, so layout survives */
    private static function tokens(string $text): array
    {
        return preg_split('/(\s+)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) ?: [];
    }

    // Spaces are not highlighted, only words.
    private static function mark(string $token): string
    {
        return trim($token) === '' ? e($token) : '<mark>'.e($token).'</mark>';
    }
}
