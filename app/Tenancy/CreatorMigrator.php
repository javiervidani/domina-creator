<?php

namespace App\Tenancy;

use Illuminate\Support\Facades\Artisan;

// Runs database/migrations/creator inside one schema. The creator role owns its schema, so no admin login is
// needed; the `migrations` table lands in that schema because it is first in the search path.
class CreatorMigrator
{
    public function __construct(private Tenancy $tenancy)
    {
    }

    public function migrate(int $creatorId): string
    {
        return $this->tenancy->run($creatorId, function () {
            Artisan::call('migrate', [
                '--path' => 'database/migrations/creator',
                '--force' => true,
            ]);

            return Artisan::output();
        });
    }
}
