<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Content extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['source_changed' => 'boolean', 'my_voice' => 'boolean'];
    }

    public function copies(): HasMany
    {
        return $this->hasMany(ContentCopy::class);
    }

    public function parts(): HasMany
    {
        return $this->hasMany(ContentPart::class)->orderBy('position');
    }

    public function targetTexts(): HasMany
    {
        return $this->hasMany(ContentTargetText::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ContentVersion::class)->latest('id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'content_tag');
    }

    // The one platform ID shown to the creator.
    public function platformId(): string
    {
        return 'CNT-'.$this->id;
    }
}
