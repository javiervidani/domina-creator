<?php

namespace App\Media;

use App\Models\ContentCopy;
use App\Models\Setting;
use App\Publishing\Publisher;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

// Keeps a SMALL copy of a post's picture, never the original and never a video. Opt-in (Settings), made right after the
// import while the network's address still works. Stored privately, one folder per creator, capped per creator.
class ImageKeeper
{
    public const SETTING = 'keep_images';
    public const MAX_SIDE = 640;       // pixels, longest side; a picture is never enlarged
    public const QUALITY = 70;         // WebP, about 25 to 60 KB for a photo
    public const MAX_DOWNLOAD = 6291456; // 6 MB; anything bigger is not kept
    public const BUDGET = 104857600;   // 100 MB per creator, roughly 2,000 pictures

    public static function enabled(): bool
    {
        return Setting::flag(self::SETTING);
    }

    public static function disk()
    {
        return Storage::disk('local');
    }

    public static function used(): int
    {
        return (int) ContentCopy::sum('kept_bytes');
    }

    /** @return string kept | skipped | full | failed */
    public function keep(ContentCopy $copy, int $creatorId): string
    {
        $url = Publisher::imageUrl($copy);
        if (! $url || $copy->kept_path) {
            return 'skipped';
        }
        if (self::used() >= self::BUDGET) {
            return 'full';
        }

        try {
            $response = Http::timeout(20)->withHeaders(['Accept' => 'image/*'])->get($url);
            $raw = $response->body();
            if (! $response->successful() || $raw === '' || strlen($raw) > self::MAX_DOWNLOAD) {
                return 'failed';
            }
            $webp = $this->shrink($raw);
        } catch (Throwable) {
            return 'failed'; // the address expired or the file is not a picture: the link stays as it was
        }

        $path = "creators/{$creatorId}/{$copy->id}.webp";
        self::disk()->put($path, $webp);
        $copy->update(['kept_path' => $path, 'kept_bytes' => strlen($webp)]);

        return 'kept';
    }

    private function shrink(string $raw): string
    {
        $image = @imagecreatefromstring($raw);
        if (! $image) {
            throw new \RuntimeException('not a picture');
        }
        [$w, $h] = [imagesx($image), imagesy($image)];
        $scale = min(1, self::MAX_SIDE / max($w, $h));
        if ($scale < 1) {
            $image = imagescale($image, max(1, (int) round($w * $scale)), max(1, (int) round($h * $scale)));
        }
        imagepalettetotruecolor($image);
        ob_start();
        imagewebp($image, null, self::QUALITY);

        return (string) ob_get_clean();
    }

    /** Switch off or "Delete kept pictures": every kept file goes, the links stay. */
    public function forgetAll(int $creatorId): void
    {
        self::disk()->deleteDirectory("creators/{$creatorId}");
        ContentCopy::whereNotNull('kept_path')->update(['kept_path' => null, 'kept_bytes' => 0]);
    }

    /** Files whose post is gone for good (Recycle bin purge) are removed so nothing is left behind. */
    public function sweep(int $creatorId): void
    {
        $keep = ContentCopy::whereNotNull('kept_path')->pluck('kept_path')->all();
        foreach (self::disk()->files("creators/{$creatorId}") as $file) {
            if (! in_array($file, $keep, true)) {
                self::disk()->delete($file);
            }
        }
    }
}
