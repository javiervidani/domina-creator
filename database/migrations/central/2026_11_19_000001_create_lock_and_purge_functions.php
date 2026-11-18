<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Run as the admin login, like provision_creator. app_user cannot revoke its own membership or drop a role, so these
// run with the admin's rights, and each one refuses unless public.tenants says the creator is in the right state.
// That check means a bug in the app cannot lock or drop a creator that is not being deleted.
return new class extends Migration
{
    public function up(): void
    {
        $this->define('lock_creator', "status = 'pending_deletion'", "EXECUTE format('REVOKE %I FROM app_user', name);");
        $this->define('unlock_creator', "status = 'pending_deletion'", "EXECUTE format('GRANT %I TO app_user', name);");
        // Purge: only after the 60 days. Drops everything the role owns, then the role.
        $this->define('purge_creator', "status = 'pending_deletion' AND purge_at <= now()", <<<'BODY'
EXECUTE format('DROP SCHEMA IF EXISTS %I CASCADE', name);
    EXECUTE format('DROP OWNED BY %I', name);
    EXECUTE format('DROP ROLE IF EXISTS %I', name);
BODY);
    }

    private function define(string $function, string $condition, string $body): void
    {
        DB::unprepared(<<<SQL
CREATE OR REPLACE FUNCTION public.{$function}(p_id bigint) RETURNS void
LANGUAGE plpgsql SECURITY DEFINER SET search_path = pg_catalog, pg_temp AS \$\$
DECLARE
    name text := 'creator_' || p_id::text;
BEGIN
    IF p_id < 1 OR NOT EXISTS (SELECT 1 FROM public.tenants WHERE creator_id = p_id AND {$condition}) THEN
        RAISE EXCEPTION '{$function}: creator % is not in the right state', p_id;
    END IF;
    {$body}
END \$\$
SQL);
        DB::statement("REVOKE ALL ON FUNCTION public.{$function}(bigint) FROM PUBLIC");
        DB::statement("GRANT EXECUTE ON FUNCTION public.{$function}(bigint) TO app_user");
    }

    public function down(): void
    {
        foreach (['lock_creator', 'unlock_creator', 'purge_creator'] as $f) {
            DB::statement("DROP FUNCTION IF EXISTS public.{$f}(bigint)");
        }
    }
};
