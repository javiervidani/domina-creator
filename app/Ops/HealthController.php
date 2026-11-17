<?php

namespace App\Ops;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * GET /ops/health: five lines that say whether the app is up and what is deployed.
 * Safe to call when the database is down, which is exactly when it is needed.
 */
class HealthController extends OpsController
{
    public function __invoke(): Response
    {
        $newest = $this->digest()->build('error', null, 1)[0] ?? null;

        return $this->text([
            sprintf('app: %s (%s, debug %s)', config('app.name'), app()->environment(), config('app.debug') ? 'ON, turn it off in production' : 'off'),
            'php: '.PHP_VERSION.', laravel: '.app()->version(),
            'commit: '.$this->commit(),
            'db: '.$this->database(),
            sprintf('log: %d KB, last error: %s', (int) round($this->reader()->size() / 1024), $newest['last'] ?? 'none'),
            'time: '.date('Y-m-d H:i:s').' '.config('app.timezone'),
        ]);
    }

    private function database(): string
    {
        try {
            DB::connection()->getPdo();

            return 'ok ('.config('database.default').')';
        } catch (Throwable $e) {
            return 'FAIL '.$this->redactor()->clean(class_basename($e).': '.$e->getMessage());
        }
    }

    /** Works when a .git folder was deployed, or when a deploy step wrote storage/app/deploy-commit. */
    private function commit(): string
    {
        $head = base_path('.git/HEAD');
        if (is_file($head)) {
            $ref = trim((string) file_get_contents($head));
            if (str_starts_with($ref, 'ref: ')) {
                $file = base_path('.git/'.substr($ref, 5));
                $ref = is_file($file) ? trim((string) file_get_contents($file)) : '';
            }

            return $ref !== '' ? substr($ref, 0, 7) : 'unknown';
        }

        $file = storage_path('app/deploy-commit');

        return is_file($file) ? substr(trim((string) file_get_contents($file)), 0, 7) : 'unknown';
    }
}
