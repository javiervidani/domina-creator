<?php

namespace App\Publishing;

use App\Models\Content;

// The text that goes to a network: its own version if the creator wrote one, otherwise the master text.
class TargetText
{
    public static function for(Content $content, string $platform): string
    {
        $own = $content->targetTexts->firstWhere('target', $platform);

        return $own ? $own->text : $content->master_text;
    }
}
