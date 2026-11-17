<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

// Lives in the creator's schema, so the table name is unqualified on purpose.
class ConnectedAccount extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_source_of_truth' => 'boolean', 'import_enabled' => 'boolean', 'last_sync_at' => 'datetime'];
    }

    public function token(): HasOne
    {
        return $this->hasOne(AccountToken::class);
    }

    public function runs(): HasMany
    {
        return $this->hasMany(SyncRun::class);
    }
}
