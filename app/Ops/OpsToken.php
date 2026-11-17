<?php

namespace App\Ops;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the ops endpoints. Every refusal is a plain 404, so nobody can tell the endpoints exist.
 * The token comes from the Authorization header or from `?k=` (some fetch tools cannot send headers).
 */
class OpsToken
{
    private const MIN_LENGTH = 32;

    public function handle(Request $request, Closure $next): Response
    {
        $token = (string) config('ops.token');

        abort_unless(
            strlen($token) >= self::MIN_LENGTH && ! $this->expired() && $this->matches($request, $token),
            404
        );

        return $next($request);
    }

    private function matches(Request $request, string $token): bool
    {
        $given = $request->bearerToken() ?? $request->query('k', '');

        return is_string($given) && $given !== '' && hash_equals($token, $given);
    }

    /** A date without a time means the end of that day, UTC. An end date that cannot be read counts as expired. */
    private function expired(): bool
    {
        $until = config('ops.expires');
        if (empty($until)) {
            return false;
        }

        $until = (string) $until;
        $ts = preg_match('/^\d{4}-\d{2}-\d{2}$/', $until) ? strtotime($until.' 23:59:59 UTC') : strtotime($until);

        return $ts === false || $ts < time();
    }
}
