<?php

namespace App\Collections;

// The sites that may show a collection in a frame or read its JSON. Pure, so it is easy to test.
class AllowedDomains
{
    private const HOST = '/^(\*\.)?([a-z0-9]([a-z0-9-]*[a-z0-9])?\.)+[a-z]{2,}$/';

    /** @return array<string> clean hosts from free text: one per line or comma, scheme and path ignored */
    public static function parse(?string $text): array
    {
        $hosts = [];
        foreach (preg_split('/[\s,]+/', strtolower((string) $text), -1, PREG_SPLIT_NO_EMPTY) as $raw) {
            $host = preg_replace('#^https?://#', '', $raw);
            $host = explode('/', $host)[0];
            if (preg_match(self::HOST, $host)) {
                $hosts[$host] = $host;
            }
        }

        return array_values($hosts);
    }

    // No domains means nobody may frame it, which is the safe default.
    public static function frameAncestors(array $hosts): string
    {
        return $hosts === [] ? "frame-ancestors 'none'" : 'frame-ancestors '.implode(' ', array_map(fn ($h) => 'https://'.$h, $hosts));
    }

    // The value for Access-Control-Allow-Origin, or null when this origin is not allowed.
    public static function corsOrigin(array $hosts, ?string $origin): ?string
    {
        $host = parse_url((string) $origin, PHP_URL_HOST);
        if (! $host || parse_url($origin, PHP_URL_SCHEME) !== 'https') {
            return null;
        }
        foreach ($hosts as $allowed) {
            if ($allowed === $host || (str_starts_with($allowed, '*.') && str_ends_with($host, substr($allowed, 1)))) {
                return 'https://'.$host;
            }
        }

        return null;
    }
}
