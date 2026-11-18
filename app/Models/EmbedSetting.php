<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmbedSetting extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['show_media' => 'boolean'];
    }
}
