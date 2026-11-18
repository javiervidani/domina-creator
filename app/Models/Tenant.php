<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $table = 'public.tenants';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['purge_at' => 'datetime'];
    }
}
