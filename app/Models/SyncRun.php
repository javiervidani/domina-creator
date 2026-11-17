<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncRun extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    public function account(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ConnectedAccount::class, 'connected_account_id');
    }

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'finished_at' => 'datetime'];
    }
}
