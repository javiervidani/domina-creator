<?php

namespace Tests\Unit;

use App\Media\ImageKeeper;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class ImageKeeperTest extends TestCase
{
    public function test_a_big_picture_becomes_a_small_webp_and_is_never_enlarged(): void
    {
        $big = imagecreatetruecolor(1600, 1200);
        ob_start();
        imagejpeg($big, null, 90);
        $raw = ob_get_clean();
        $small = imagecreatetruecolor(300, 200);
        ob_start();
        imagejpeg($small, null, 90);
        $rawSmall = ob_get_clean();

        $shrink = new ReflectionMethod(ImageKeeper::class, 'shrink');
        $keeper = new ImageKeeper;

        $out = getimagesizefromstring($shrink->invoke($keeper, $raw));
        $this->assertSame([640, 480, 'image/webp'], [$out[0], $out[1], $out['mime']]);

        $out = getimagesizefromstring($shrink->invoke($keeper, $rawSmall));
        $this->assertSame([300, 200], [$out[0], $out[1]]);
    }

    public function test_something_that_is_not_a_picture_is_refused(): void
    {
        $this->expectException(\RuntimeException::class);
        (new ReflectionMethod(ImageKeeper::class, 'shrink'))->invoke(new ImageKeeper, 'not a picture');
    }
}
