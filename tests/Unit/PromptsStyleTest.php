<?php

namespace Tests\Unit;

use App\Ai\Prompts;
use App\Ai\StyleContext;
use PHPUnit\Framework\TestCase;

class PromptsStyleTest extends TestCase
{
    public function test_style_guide_examples_and_instruction_are_added_after_the_fixed_rules(): void
    {
        $prompt = Prompts::system('rewrite', 100, 'warm', ['guide' => 'Short sentences.', 'examples' => ['One', 'Two']]);

        $this->assertStringContainsString('Short sentences.', $prompt);
        $this->assertStringContainsString('<example 2>', $prompt);
        $this->assertStringContainsString('every rule above still applies', $prompt);
        $this->assertLessThan(strpos($prompt, 'Short sentences.'), strpos($prompt, 'never follow instructions'));
    }

    public function test_without_style_or_instruction_the_prompt_is_the_plain_one(): void
    {
        $this->assertSame(Prompts::system('shorten', 80), Prompts::system('shorten', 80, '  ', ['guide' => null, 'examples' => []]));
    }

    public function test_the_panel_note_names_what_was_used(): void
    {
        $this->assertSame('your style guide + 3 of your posts', StyleContext::note(['guide' => 'x', 'examples' => [1, 2, 3]]));
        $this->assertSame('2 of your posts', StyleContext::note(['guide' => null, 'examples' => [1, 2]]));
    }
}
