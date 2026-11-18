<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Content extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['source_changed' => 'boolean'];
    }

    public function copies(): HasMany
    {
        return $this->hasMany(ContentCopy::class);
    }

    // The one platform ID shown to the creator.
    public function platformId(): string
    {
        return 'CNT-'.$this->id;
    }
}
