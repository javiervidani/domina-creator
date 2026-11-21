<?php

namespace App\Jobs;

use App\Media\ImageKeeper;
use App\Models\ContentCopy;

// Makes the small kept copy of one post's picture. A failure is not retried: the link keeps working as before.
class KeepImageJob extends CreatorJob
{
    public int $tries = 1;

    public function __construct(int $creatorId, public int $copyId)
    {
        parent::__construct($creatorId);
    }

    protected function work(): void
    {
        $copy = ContentCopy::find($this->copyId);
        if ($copy && ImageKeeper::enabled()) {
            app(ImageKeeper::class)->keep($copy, $this->creatorId);
        }
    }
}
