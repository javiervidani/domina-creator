<?php

namespace Tests\Unit;

use App\Accounts\AccountHealth;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class AccountHealthTest extends TestCase
{
    public function test_health_levels(): void
    {
        $now = new DateTimeImmutable('2026-11-17');

        $this->assertSame('ok', AccountHealth::for('ok', null, $now));
        $this->assertSame('ok', AccountHealth::for('ok', new DateTimeImmutable('2026-12-30'), $now));
        $this->assertSame('expiring', AccountHealth::for('ok', new DateTimeImmutable('2026-11-20'), $now));
        $this->assertSame('reconnect', AccountHealth::for('ok', new DateTimeImmutable('2026-11-01'), $now));
        $this->assertSame('reconnect', AccountHealth::for('reconnect', new DateTimeImmutable('2027-01-01'), $now));
    }
}
