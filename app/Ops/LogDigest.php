<?php

namespace App\Ops;

/**
 * Turns raw log entries into a short list: one row per distinct problem, with a count.
 * Repeated errors, vendor stack frames and long traces are what make logs expensive to read, so they are folded away.
 */
class LogDigest
{
    private const RANK = ['debug' => 0, 'info' => 1, 'notice' => 2, 'warning' => 3, 'error' => 4, 'critical' => 5, 'alert' => 6, 'emergency' => 7];

    /** @param string $basePath application root with a trailing slash, removed from file paths in the output */
    public function __construct(private LogReader $reader, private string $basePath)
    {
    }

    /**
     * Newest problem first.
     *
     * @return list<array{level:string, what:string, where:string, count:int, first:string, last:string}>
     */
    public function build(string $minLevel = 'error', ?string $since = null, int $limit = 10): array
    {
        $min = self::RANK[$minLevel] ?? self::RANK['error'];
        $sinceTs = $since !== null ? (strtotime($since) ?: null) : null;
        $groups = [];

        foreach ($this->reader->entries() as $entry) {
            $ts = strtotime($entry['time']) ?: 0;
            if ((self::RANK[$entry['level']] ?? 0) < $min || ($sinceTs !== null && $ts < $sinceTs)) {
                continue;
            }

            $what = $this->what($entry['head']);
            $where = $this->where($entry);
            $key = $entry['level'].'|'.sha1(preg_replace('/\d+/', '#', $what).'|'.$where);

            $groups[$key] ??= ['level' => $entry['level'], 'what' => $what, 'where' => $where, 'count' => 0, 'first' => $entry['time'], 'last' => $entry['time'], 'ts' => $ts];
            $groups[$key]['count']++;
            $groups[$key]['last'] = $entry['time'];
            $groups[$key]['ts'] = $ts;
        }

        usort($groups, fn (array $a, array $b) => $b['ts'] <=> $a['ts']);

        return array_map(
            fn (array $g) => array_diff_key($g, ['ts' => 1]),
            array_slice($groups, 0, max(1, $limit))
        );
    }

    /** The log message, with the exception class in front when there is one. */
    private function what(string $head): string
    {
        $cut = strstr($head, ' {"exception"', true);
        $what = $cut === false ? $head : $cut;

        if (preg_match('/\[object\] \(([\w\\\\]+)\(code:/', $head, $m)) {
            $class = str_replace('\\\\', '\\', $m[1]);
            $short = ltrim((string) strrchr('\\'.$class, '\\'), '\\');
            $what = $short.': '.$what;
        }

        return $what;
    }

    /** First file in the application code, not vendor, as `path/File.php:line`. Falls back to the first file mentioned. */
    private function where(array $entry): string
    {
        $text = $entry['head']."\n".implode("\n", $entry['trace']);
        if (! preg_match_all('#(/[^\s():]+\.php)(?::|\()(\d+)#', $text, $all, PREG_SET_ORDER)) {
            return '';
        }

        $found = $all[0];
        foreach ($all as $match) {
            if (! str_contains($match[1], '/vendor/')) {
                $found = $match;
                break;
            }
        }

        $file = str_starts_with($found[1], $this->basePath)
            ? substr($found[1], strlen($this->basePath))
            : basename($found[1]);

        return $file.':'.$found[2];
    }
}
