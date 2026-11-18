<?php

namespace App\Collections;

use App\Models\Collection;
use App\Models\FeedToken;
use Illuminate\Support\Str;

// A link token is "<creator id>.<random>". The id is not a secret: it only says which schema to look in, and the
// random part is what proves the link. Only a hash of it is searched, and a revoked token stops working at once.
class TokenIssuer
{
    public function issue(int $creatorId, Collection $collection): FeedToken
    {
        $collection->tokens()->whereNull('revoked_at')->update(['revoked_at' => now()]);
        $token = $creatorId.'.'.Str::random(40);

        return FeedToken::create(['collection_id' => $collection->id, 'token' => $token, 'token_hash' => self::hash($token)]);
    }

    public static function hash(string $token): string
    {
        return hash('sha256', $token);
    }

    public static function creatorId(string $token): ?int
    {
        return preg_match('/^(\d{1,9})\.[A-Za-z0-9]{40}$/', $token, $m) ? (int) $m[1] : null;
    }

    public function find(string $token): ?FeedToken
    {
        return FeedToken::where('token_hash', self::hash($token))->whereNull('revoked_at')->first();
    }
}
