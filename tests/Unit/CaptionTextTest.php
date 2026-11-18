<?php

namespace Tests\Unit;

use App\Import\CaptionText;
use PHPUnit\Framework\TestCase;

class CaptionTextTest extends TestCase
{
    public function test_change_hash_ignores_only_line_endings_and_edges(): void
    {
        $this->assertSame(CaptionText::changeHash("a\r\nb\r\n"), CaptionText::changeHash("a\nb"));
        $this->assertNotSame(CaptionText::changeHash('Try it for a week'), CaptionText::changeHash('Try it for two weeks'));
    }

    public function test_match_hash_ignores_case_and_spacing(): void
    {
        $this->assertSame(CaptionText::matchHash('Hello   World'), CaptionText::matchHash("hello\nworld"));
        $this->assertNotSame(CaptionText::matchHash('a'), CaptionText::matchHash('b'));
    }

    public function test_title_is_the_first_sentence_or_a_cut_first_line(): void
    {
        $this->assertSame('Water first.', CaptionText::title('Water first. Then light.'));
        $this->assertSame('Instagram post', CaptionText::title(''));
        $this->assertLessThanOrEqual(80, mb_strlen(CaptionText::title(str_repeat('word ', 40))));
        $this->assertSame('No end line', CaptionText::title("No end line\nsecond"));
    }
}
