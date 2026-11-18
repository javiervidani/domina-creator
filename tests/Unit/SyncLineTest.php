<?php

namespace Tests\Unit;

use App\Dashboard\SyncLine;
use PHPUnit\Framework\TestCase;

class SyncLineTest extends TestCase
{
    public function test_line_names_each_target_and_its_mode(): void
    {
        $line = SyncLine::format('instagram', 'osnat', [
            ['platform' => 'telegram', 'handle' => 'Channel', 'mode' => 'review'],
            ['platform' => 'youtube', 'handle' => 'Main', 'mode' => 'auto'],
        ]);

        $this->assertSame('Instagram @osnat to Telegram Channel (review), Youtube Main (auto)', $line);
    }

    public function test_no_targets(): void
    {
        $this->assertSame('Instagram @osnat: no targets yet', SyncLine::format('instagram', 'osnat', []));
    }
}
