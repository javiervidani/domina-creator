<?php

namespace App\Http\Controllers;

use App\Media\ImageKeeper;
use App\Models\ContentCopy;

// Shows a kept picture to the signed-in creator only. The file is private; there is no public address for it.
class MediaController extends Controller
{
    public function show(ContentCopy $copy)
    {
        abort_unless($copy->kept_path && ImageKeeper::disk()->exists($copy->kept_path), 404);

        return response(ImageKeeper::disk()->get($copy->kept_path), 200, ['Content-Type' => 'image/webp', 'Cache-Control' => 'private, max-age=86400']);
    }
}
