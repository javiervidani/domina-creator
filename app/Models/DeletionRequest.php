<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeletionRequest extends Model
{
    protected $table = 'public.deletion_requests';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['purge_at' => 'datetime', 'restored_at' => 'datetime', 'purged_at' => 'datetime'];
    }
}
