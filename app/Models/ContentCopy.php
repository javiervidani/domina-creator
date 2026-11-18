<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentCopy extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_origin' => 'boolean', 'remote_published_at' => 'datetime', 'published_at' => 'datetime'];
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }
}
