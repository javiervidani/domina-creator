<?php

namespace Tests\Unit;

use App\Content\TextDiff;
use Tests\TestCase;

class TextDiffTest extends TestCase
{
    public function test_marks_only_the_words_that_differ(): void
    {
        $d = TextDiff::sides('Try it for a week', 'Try it for two weeks');

        $this->assertStringContainsString('<mark>a</mark>', $d['mine']);
        $this->assertStringContainsString('<mark>week</mark>', $d['mine']);
        $this->assertStringContainsString('<mark>two</mark>', $d['theirs']);
        $this->assertStringNotContainsString('<mark>Try</mark>', $d['mine']);
    }

    public function test_identical_texts_have_no_marks(): void
    {
        $d = TextDiff::sides('same text', 'same text');

        $this->assertStringNotContainsString('<mark>', $d['mine'].$d['theirs']);
    }

    public function test_html_in_the_text_is_escaped(): void
    {
        $this->assertStringNotContainsString('<script>', TextDiff::sides('<script>x</script>', 'y')['mine']);
    }
}
