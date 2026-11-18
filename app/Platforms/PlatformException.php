<?php

namespace App\Platforms;

use RuntimeException;

class PlatformException extends RuntimeException
{
    // needsReconnect: the token is dead, only a new sign-in helps. retryable: a temporary failure worth another try.
    public function __construct(string $message, public readonly bool $needsReconnect = false, public readonly bool $retryable = false)
    {
        parent::__construct($message);
    }
}
