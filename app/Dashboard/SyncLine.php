<?php

namespace App\Dashboard;

// The long sync line on the dashboard, for example "Instagram @osnat to Telegram Channel (review), YouTube (auto)".
// Pure formatting, so it is easy to test.
class SyncLine
{
    /**
     * @param  array<array{platform: string, handle: string, mode: string}>  $targets
     */
    public static function format(string $sourcePlatform, string $sourceHandle, array $targets): string
    {
        $source = ucfirst($sourcePlatform).' @'.$sourceHandle;
        if ($targets === []) {
            return $source.': no targets yet';
        }

        return $source.' to '.implode(', ', array_map(
            fn ($t) => ucfirst($t['platform']).' '.$t['handle'].' ('.($t['mode'] === 'auto' ? 'auto' : 'review').')',
            $targets,
        ));
    }
}
