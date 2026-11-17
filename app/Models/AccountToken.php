<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountToken extends Model
{
    protected $guarded = [];

    protected $hidden = ['access_token', 'refresh_token'];

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'expires_at' => 'datetime',
            'refreshed_at' => 'datetime',
        ];
    }
}
