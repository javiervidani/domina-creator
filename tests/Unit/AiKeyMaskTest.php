<?php

namespace Tests\Unit;

use App\Models\AiKey;
use PHPUnit\Framework\TestCase;

class AiKeyMaskTest extends TestCase
{
    public function test_only_the_last_four_characters_show(): void
    {
        $this->assertSame('••••wxyz', AiKey::mask('sk-ant-abcdefghijklmnopwxyz'));
        $this->assertStringNotContainsString('abcdef', AiKey::mask('sk-ant-abcdefghijklmnopwxyz'));
    }

    public function test_a_short_key_shows_nothing_of_itself(): void
    {
        $this->assertSame('••••', AiKey::mask('short'));
    }
}
