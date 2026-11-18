<?php

namespace Tests\Unit;

use App\Collections\AllowedDomains;
use App\Collections\TokenIssuer;
use PHPUnit\Framework\TestCase;

class AllowedDomainsTest extends TestCase
{
    public function test_parse_keeps_only_valid_hosts(): void
    {
        $this->assertSame(['example.com', 'shop.example.org', '*.blog.io'],
            AllowedDomains::parse("https://Example.com/path, shop.example.org\n*.blog.io  bad_host localhost"));
        $this->assertSame([], AllowedDomains::parse(null));
    }

    public function test_frame_ancestors_default_to_nobody(): void
    {
        $this->assertSame("frame-ancestors 'none'", AllowedDomains::frameAncestors([]));
        $this->assertSame('frame-ancestors https://a.com https://*.b.io', AllowedDomains::frameAncestors(['a.com', '*.b.io']));
    }

    public function test_cors_only_for_allowed_https_origins(): void
    {
        $hosts = ['a.com', '*.b.io'];

        $this->assertSame('https://x.b.io', AllowedDomains::corsOrigin($hosts, 'https://x.b.io'));
        $this->assertSame('https://a.com', AllowedDomains::corsOrigin($hosts, 'https://a.com'));
        $this->assertNull(AllowedDomains::corsOrigin($hosts, 'https://evil.com'));
        $this->assertNull(AllowedDomains::corsOrigin($hosts, 'http://a.com'));
        $this->assertNull(AllowedDomains::corsOrigin($hosts, 'https://b.io'));
        $this->assertNull(AllowedDomains::corsOrigin($hosts, null));
    }

    public function test_token_names_the_creator_and_rejects_malformed_ones(): void
    {
        $this->assertSame(12, TokenIssuer::creatorId('12.'.str_repeat('a', 40)));
        $this->assertNull(TokenIssuer::creatorId('12.short'));
        $this->assertNull(TokenIssuer::creatorId('x.'.str_repeat('a', 40)));
        $this->assertSame(64, strlen(TokenIssuer::hash('anything')));
    }
}
