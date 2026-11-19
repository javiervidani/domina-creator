<?php

namespace Tests\Unit;

use App\Models\ContentCopy;
use App\Platforms\Telegram\TelegramError;
use App\Publishing\Publisher;
use PHPUnit\Framework\TestCase;

class PublisherImageTest extends TestCase
{
    public function test_the_picture_is_the_post_image_or_a_videos_cover(): void
    {
        $image = new ContentCopy(['media_type' => 'IMAGE', 'media_url' => 'https://x/p.jpg', 'thumbnail_url' => 'https://x/t.jpg']);
        $video = new ContentCopy(['media_type' => 'VIDEO', 'media_url' => 'https://x/v.mp4', 'thumbnail_url' => 'https://x/t.jpg']);

        $this->assertSame('https://x/p.jpg', Publisher::imageUrl($image));
        $this->assertSame('https://x/t.jpg', Publisher::imageUrl($video));
        $this->assertNull(Publisher::imageUrl(null));
        $this->assertNull(Publisher::imageUrl(new ContentCopy(['media_type' => 'IMAGE'])));
    }

    public function test_picture_download_errors_explain_what_to_do(): void
    {
        $message = TelegramError::explain(400, 'Bad Request: failed to get HTTP URL content');

        $this->assertStringContainsString('Sync now', $message);
        $this->assertStringContainsString('failed to get HTTP URL content', $message);
    }
}
