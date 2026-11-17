<?php

namespace App\Ops;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * GET /ops/logs?level=error&limit=10&since=2026-11-17 14:00
 * A digest of the log: one line per distinct problem, newest first, secrets masked, each line cut short.
 */
class LogsController extends OpsController
{
    public function __invoke(Request $request): Response
    {
        $level = strtolower((string) $request->query('level', 'error'));
        $limit = min(max((int) $request->query('limit', 10), 1), (int) config('ops.max_entries'));
        $since = $request->query('since');

        $groups = $this->digest()->build($level, is_string($since) ? $since : null, $limit);
        $redactor = $this->redactor();

        $lines = [sprintf(
            'log %d KB, tz %s, level >= %s, showing %d',
            (int) round($this->reader()->size() / 1024),
            config('app.timezone'),
            $level,
            count($groups)
        )];

        foreach ($groups as $group) {
            $lines[] = $redactor->clean(sprintf(
                '[%s%s] %s (first %s) %s%s',
                strtoupper($group['level']),
                $group['count'] > 1 ? ' x'.$group['count'] : '',
                $group['last'],
                $group['first'],
                $group['what'],
                $group['where'] !== '' ? ' @ '.$group['where'] : ''
            ));
        }

        if ($groups === []) {
            $lines[] = 'no matching entries';
        }

        return $this->text($lines);
    }
}
