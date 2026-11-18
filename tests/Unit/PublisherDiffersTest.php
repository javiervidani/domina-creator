<?php

namespace Tests\Unit;

use App\Import\CaptionText;
use App\Models\ContentCopy;
use App\Publishing\Publisher;
use PHPUnit\Framework\TestCase;

class PublisherDiffersTest extends TestCase
{
    public function test_a_published_copy_differs_only_when_the_text_changed(): void
    {
        $copy = new ContentCopy(['status' => 'published', 'published_hash' => CaptionText::changeHash('Hello')]);

        $this->assertFalse(Publisher::differs($copy, "Hello\r\n"));
        $this->assertTrue(Publisher::differs($copy, 'Hello again'));
    }

    public function test_drafts_and_failed_copies_never_show_as_differing(): void
    {
        $this->assertFalse(Publisher::differs(new ContentCopy(['status' => 'draft']), 'anything'));
        $this->assertFalse(Publisher::differs(new ContentCopy(['status' => 'failed']), 'anything'));
    }

    public function test_only_telegram_is_supported_for_now(): void
    {
        $this->assertTrue(Publisher::supports('telegram'));
        $this->assertFalse(Publisher::supports('youtube'));
    }
}
