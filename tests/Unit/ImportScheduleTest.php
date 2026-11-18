<?php

namespace Tests\Unit;

use App\Import\ImportSchedule;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class ImportScheduleTest extends TestCase
{
    public function test_due_by_frequency(): void
    {
        $now = new DateTimeImmutable('2026-11-18 12:00');

        $this->assertTrue(ImportSchedule::isDue('hourly', null, $now));
        $this->assertFalse(ImportSchedule::isDue('hourly', new DateTimeImmutable('2026-11-18 11:30'), $now));
        $this->assertTrue(ImportSchedule::isDue('hourly', new DateTimeImmutable('2026-11-18 10:59'), $now));
        $this->assertFalse(ImportSchedule::isDue('daily', new DateTimeImmutable('2026-11-18 00:00'), $now));
        $this->assertTrue(ImportSchedule::isDue('fifteen_minutes', new DateTimeImmutable('2026-11-18 11:44'), $now));
    }
}
