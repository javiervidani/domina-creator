<?php

namespace App\Accounts;

use App\Models\ConnectedAccount;

// What the Accounts page shows as the health badge. Pure logic so it can be tested without a database.
class AccountHealth
{
    public const EXPIRING_DAYS = 10;

    public static function for(string $status, ?\DateTimeInterface $expiresAt, ?\DateTimeInterface $now = null): string
    {
        $now ??= new \DateTimeImmutable;

        if ($status === 'reconnect' || ($expiresAt && $expiresAt <= $now)) {
            return 'reconnect';
        }
        if ($expiresAt && $expiresAt <= (new \DateTimeImmutable($now->format('c')))->modify('+'.self::EXPIRING_DAYS.' days')) {
            return 'expiring';
        }

        return 'ok';
    }

    public static function forAccount(ConnectedAccount $account): string
    {
        return self::for($account->status, $account->token?->expires_at);
    }
}
