<?php

namespace App\Ops;

/**
 * Reads the end of a Laravel log file and splits it into entries.
 * Only the last N bytes are read, so a huge log costs neither memory nor tokens.
 */
class LogReader
{
    private const HEADER = '/^\[(\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2}[^\]]*)\] (\w+)\.([A-Za-z]+): (.*)$/';

    public function __construct(private string $path, private int $tailBytes = 262144)
    {
    }

    public function exists(): bool
    {
        return is_file($this->path) && is_readable($this->path);
    }

    public function size(): int
    {
        return $this->exists() ? (int) filesize($this->path) : 0;
    }

    /**
     * Oldest first. A line that does not start a new entry belongs to the one above it (stack trace lines).
     *
     * @return list<array{time:string, env:string, level:string, head:string, trace:list<string>}>
     */
    public function entries(): array
    {
        $entries = [];
        $current = null;

        foreach (preg_split('/\R/', $this->tail()) ?: [] as $line) {
            if (preg_match(self::HEADER, $line, $m)) {
                if ($current !== null) {
                    $entries[] = $current;
                }
                $current = ['time' => $m[1], 'env' => $m[2], 'level' => strtolower($m[3]), 'head' => $m[4], 'trace' => []];
            } elseif ($current !== null && $line !== '') {
                $current['trace'][] = $line;
            }
        }

        if ($current !== null) {
            $entries[] = $current;
        }

        return $entries;
    }

    /** The cut at the start of the tail usually lands mid-entry; those lines are dropped because no header precedes them. */
    private function tail(): string
    {
        if (! $this->exists()) {
            return '';
        }

        $handle = fopen($this->path, 'rb');
        if ($handle === false) {
            return '';
        }

        fseek($handle, max(0, $this->size() - $this->tailBytes));
        $text = (string) stream_get_contents($handle);
        fclose($handle);

        return $text;
    }
}
