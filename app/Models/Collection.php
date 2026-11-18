<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Collection extends Model
{
    use SoftDeletes;

    public const VISIBILITY = ['private' => 'Private', 'link' => 'Anyone with the link', 'public' => 'Public'];

    protected $guarded = [];

    public function items(): HasMany
    {
        return $this->hasMany(CollectionItem::class)->orderBy('position');
    }

    public function embed(): HasOne
    {
        return $this->hasOne(EmbedSetting::class);
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(FeedToken::class);
    }
}
