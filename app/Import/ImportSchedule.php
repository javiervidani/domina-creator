<?php

namespace App\Import;

use DateTimeInterface;

class ImportSchedule
{
    private const MINUTES = ['fifteen_minutes' => 15, 'hourly' => 60, 'twicedaily' => 720, 'daily' => 1440];

    public static function isDue(string $frequency, ?DateTimeInterface $lastSync, DateTimeInterface $now): bool
    {
        if ($lastSync === null) {
            return true;
        }
        $minutes = self::MINUTES[$frequency] ?? 60;

        return $now->getTimestamp() - $lastSync->getTimestamp() >= $minutes * 60;
    }
}
