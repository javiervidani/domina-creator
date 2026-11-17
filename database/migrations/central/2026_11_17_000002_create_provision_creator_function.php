<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Run as the admin login (CREATEROLE). The function runs with its owner's rights, so app_user can create a
// creator's role and schema on demand without ever holding CREATEROLE itself.
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION public.provision_creator(p_id bigint) RETURNS text
LANGUAGE plpgsql SECURITY DEFINER SET search_path = pg_catalog, pg_temp AS $$
DECLARE
    name text := 'creator_' || p_id::text;
BEGIN
    IF p_id < 1 THEN
        RAISE EXCEPTION 'invalid creator id';
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = name) THEN
        EXECUTE format('CREATE ROLE %I NOLOGIN', name);
    END IF;
    -- The owner needs SET on the role to create a schema owned by it.
    EXECUTE format('GRANT %I TO %I', name, current_user);
    EXECUTE format('GRANT %I TO app_user', name);
    EXECUTE format('CREATE SCHEMA IF NOT EXISTS %I AUTHORIZATION %I', name, name);
    RETURN name;
END $$
SQL);
        DB::statement('REVOKE ALL ON FUNCTION public.provision_creator(bigint) FROM PUBLIC');
        DB::statement('GRANT EXECUTE ON FUNCTION public.provision_creator(bigint) TO app_user');
    }

    public function down(): void
    {
        DB::statement('DROP FUNCTION IF EXISTS public.provision_creator(bigint)');
    }
};
