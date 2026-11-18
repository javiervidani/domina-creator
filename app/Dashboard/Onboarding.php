<?php

namespace App\Dashboard;

use App\Models\AccountTarget;
use App\Models\ConnectedAccount;
use App\Models\Content;

// The wizard keeps no state of its own: each step is done when the data says so.
// The one exception is "skip targets", which the controller remembers in the session.
class Onboarding
{
    /** @return array<int, array{key: string, title: string, done: bool}> */
    public static function steps(bool $targetsSkipped): array
    {
        $accounts = ConnectedAccount::count();

        return [
            ['key' => 'connect', 'title' => 'Connect your Instagram account', 'done' => ConnectedAccount::where('platform', 'instagram')->exists()],
            ['key' => 'source', 'title' => 'Choose your source of truth', 'done' => ConnectedAccount::where('is_source_of_truth', true)->exists()],
            ['key' => 'targets', 'title' => 'Choose where content goes', 'done' => $targetsSkipped || AccountTarget::exists()],
            ['key' => 'import', 'title' => 'Import your first post', 'done' => $accounts > 0 && Content::exists()],
        ];
    }

    public static function complete(bool $targetsSkipped): bool
    {
        return collect(self::steps($targetsSkipped))->every(fn ($s) => $s['done']);
    }
}
