<?php

namespace Tests\Unit;

use App\Platforms\YouTube\Srt;
use PHPUnit\Framework\TestCase;

class SrtTest extends TestCase
{
    public function test_captions_become_plain_text_with_a_paragraph_at_each_long_pause(): void
    {
        $srt = "1\n00:00:01,000 --> 00:00:03,000\nשלום <i>לכולם</i>\n\n2\n00:00:03,100 --> 00:00:05,000\nזה &amp; זה\n\n3\n00:00:09,000 --> 00:00:10,000\nפסקה חדשה\n";

        $this->assertSame("שלום לכולם זה & זה\n\nפסקה חדשה", Srt::toText($srt));
        $this->assertSame('', Srt::toText(''));
    }

    public function test_uploaded_tracks_beat_automatic_ones_and_hebrew_comes_first(): void
    {
        $tracks = [['id' => 'a', 'language' => 'en', 'kind' => 'standard'], ['id' => 'b', 'language' => 'he', 'kind' => 'asr'], ['id' => 'c', 'language' => 'he', 'kind' => 'standard']];

        $this->assertSame('c', Srt::choose($tracks)['id']);
        $this->assertNull(Srt::choose([]));
    }
}
