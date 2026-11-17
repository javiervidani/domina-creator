<?php

namespace App\Tenancy;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

// Creates a creator's role and schema through the SQL function, then runs the creator migrations.
class Provisioner
{
    public function __construct(private CreatorMigrator $migrator)
    {
    }

    public function provision(int $creatorId): Tenant
    {
        $schema = DB::selectOne('select public.provision_creator(?) as name', [$creatorId])->name;

        $tenant = Tenant::firstOrCreate(
            ['creator_id' => $creatorId],
            ['schema_name' => $schema, 'status' => 'active'],
        );

        $this->migrator->migrate($creatorId);

        return $tenant;
    }
}
