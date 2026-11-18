<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedToken extends Model
{
    protected $guarded = [];

    protected $hidden = ['token'];

    protected function casts(): array
    {
        return ['token' => 'encrypted', 'revoked_at' => 'datetime'];
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }
}
