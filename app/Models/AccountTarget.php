<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountTarget extends Model
{
    protected $guarded = [];

    public function source(): BelongsTo
    {
        return $this->belongsTo(ConnectedAccount::class, 'source_account_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(ConnectedAccount::class, 'target_account_id');
    }
}
