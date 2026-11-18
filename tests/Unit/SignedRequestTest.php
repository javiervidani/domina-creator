<?php

namespace Tests\Unit;

use App\Platforms\Instagram\SignedRequest;
use PHPUnit\Framework\TestCase;

class SignedRequestTest extends TestCase
{
    private function sign(array $data, string $secret): string
    {
        $b64 = fn (string $s) => rtrim(strtr(base64_encode($s), '+/', '-_'), '=');
        $payload = $b64(json_encode($data));

        return $b64(hash_hmac('sha256', $payload, $secret, true)).'.'.$payload;
    }

    public function test_valid_signature_returns_the_payload(): void
    {
        $this->assertSame(['user_id' => '42'], SignedRequest::parse($this->sign(['user_id' => '42'], 'secret'), 'secret'));
    }

    public function test_wrong_secret_garbage_and_empty_secret_are_rejected(): void
    {
        $signed = $this->sign(['user_id' => '42'], 'secret');

        $this->assertNull(SignedRequest::parse($signed, 'other'));
        $this->assertNull(SignedRequest::parse('garbage', 'secret'));
        $this->assertNull(SignedRequest::parse($signed, ''));
    }
}
