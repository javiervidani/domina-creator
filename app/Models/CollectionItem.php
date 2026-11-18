<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionItem extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }
}
