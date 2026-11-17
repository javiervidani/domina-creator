<?php

namespace App\Platforms;

use RuntimeException;

class PlatformException extends RuntimeException
{
    // Instagram code 190 means the token is dead, so only a new sign-in helps.
    public function __construct(string $message, public readonly bool $needsReconnect = false)
    {
        parent::__construct($message);
    }
}
