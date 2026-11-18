<?php

namespace App\Platforms\Instagram;

// Meta signs its deauthorize and data-deletion calls: "<signature>.<payload>", both base64url, HMAC-SHA256 with
// the app secret over the encoded payload. Anything that does not verify is ignored.
class SignedRequest
{
    public static function parse(string $signed, string $secret): ?array
    {
        if ($secret === '' || ! str_contains($signed, '.')) {
            return null;
        }

        [$signature, $payload] = explode('.', $signed, 2);
        $expected = hash_hmac('sha256', $payload, $secret, true);

        if (! hash_equals($expected, self::decode($signature))) {
            return null;
        }

        $data = json_decode(self::decode($payload), true);

        return is_array($data) ? $data : null;
    }

    private static function decode(string $value): string
    {
        return (string) base64_decode(strtr($value, '-_', '+/'));
    }
}
