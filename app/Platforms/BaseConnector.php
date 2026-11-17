<?php

namespace App\Platforms;

// One small class per network. Subclasses add only what that network needs; nothing else is assumed here.
abstract class BaseConnector
{
    public function __construct(protected string $accessToken)
    {
    }

    abstract public static function key(): string;

    /** @return array{items: array, after: string, has_next: bool} */
    abstract public function mediaPage(string $after = ''): array;

    /** @return array{access_token: string, expires_in: int} */
    abstract public function refreshToken(): array;
}
