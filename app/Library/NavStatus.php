<?php

namespace App\Library;

use App\Models\ConnectedAccount;
use App\Models\Content;

// What the top bar shows: the sync chip, its dropdown, and the drafts badge.
class NavStatus
{
    public function data(): array
    {
        $accounts = ConnectedAccount::orderBy('id')->get(['id', 'platform', 'handle', 'status', 'last_sync_at']);
        $last = $accounts->max('last_sync_at');

        return [
            'accounts' => $accounts,
            'chip' => $accounts->isEmpty() ? 'No accounts' : ($last ? 'Synced '.$last->diffForHumans(short: true) : 'Not synced yet'),
            'chipOk' => $accounts->isNotEmpty() && $accounts->every(fn ($a) => $a->status === 'ok'),
            'drafts' => Content::where('status', 'draft')->count(),
        ];
    }
}
