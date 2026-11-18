<?php

namespace App\Content;

// The networks a text can be written for, with the character limit each one shows.
class Targets
{
    public const LIMITS = ['youtube' => 5000, 'telegram' => 4096];

    public const LABELS = ['youtube' => 'YouTube', 'telegram' => 'Telegram'];

    public static function valid(string $target): bool
    {
        return isset(self::LIMITS[$target]);
    }
}
