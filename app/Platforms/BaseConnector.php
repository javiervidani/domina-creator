<?php

namespace App\Platforms;

// One small class per network. Subclasses add only what that network needs; nothing else is assumed here.
abstract class BaseConnector
{
    public function __construct(protected string $accessToken)
    {
    }

    abstract public static function key(): string;
}
