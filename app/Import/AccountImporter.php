<?php

namespace App\Import;

use App\Models\ConnectedAccount;
use App\Platforms\BaseConnector;

// Walks an account's posts, newest first, and hands each to the ContentImporter.
class AccountImporter
{
    public function __construct(private ContentImporter $importer)
    {
    }

    /** @return array{seen: int, created: int, linked: int, changed: int, skipped: int} */
    public function run(ConnectedAccount $account, BaseConnector $connector): array
    {
        $counts = ['seen' => 0, 'created' => 0, 'linked' => 0, 'changed' => 0, 'skipped' => 0];
        // First run reads deep history. Later runs stop once a whole page is older than a week before the last run,
        // which still catches recent caption edits without walking the whole account every time.
        $since = $account->last_sync_at?->copy()->subDays(7)->getTimestamp();
        $maxPages = $since ? 4 : 40;
        $after = '';

        for ($i = 0; $i < $maxPages; $i++) {
            $page = $connector->mediaPage($after);
            $allOld = $since !== null;

            foreach ($page['items'] as $item) {
                $counts['seen']++;
                $result = $this->importer->import($account, $item);
                if (isset($counts[$result])) {
                    $counts[$result]++;
                }
                $time = strtotime((string) ($item['timestamp'] ?? ''));
                if ($since === null || $time === false || $time >= $since) {
                    $allOld = false;
                }
            }

            if (! $page['has_next'] || $page['after'] === '' || $allOld) {
                break;
            }
            $after = $page['after'];
        }

        return $counts;
    }
}
