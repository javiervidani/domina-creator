<?php

namespace Tests\Unit;

use App\Ops\LogDigest;
use App\Ops\LogReader;
use App\Ops\Redactor;
use PHPUnit\Framework\TestCase;

class OpsDigestTest extends TestCase
{
    private function log(string $body): string
    {
        $path = tempnam(sys_get_temp_dir(), 'ops');
        file_put_contents($path, $body);

        return $path;
    }

    public function test_groups_repeated_errors_and_skips_low_levels(): void
    {
        $path = $this->log(
            "[2026-11-17 10:00:00] production.ERROR: Boom 1 {\"exception\":\"x\"}\n#0 /app/app/Foo.php(12): bar()\n".
            "[2026-11-17 10:05:00] production.ERROR: Boom 2 {\"exception\":\"x\"}\n#0 /app/app/Foo.php(12): bar()\n".
            "[2026-11-17 10:06:00] production.INFO: fine\n"
        );

        $rows = (new LogDigest(new LogReader($path), '/app/'))->build('error');

        $this->assertCount(1, $rows);
        $this->assertSame(2, $rows[0]['count']);
        $this->assertSame('app/Foo.php:12', $rows[0]['where']);
        unlink($path);
    }

    public function test_redactor_masks_secrets(): void
    {
        $out = (new Redactor(['supersecretvalue']))->clean('fail Authorization: Bearer abc123 supersecretvalue a@b.com');

        $this->assertStringNotContainsString('supersecretvalue', $out);
        $this->assertStringNotContainsString('abc123', $out);
        $this->assertStringNotContainsString('a@b.com', $out);
    }
}
