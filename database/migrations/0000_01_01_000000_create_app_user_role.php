<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Run with the admin login: php artisan migrate --database=pgsql_admin
// Creates the login the app runs as. The password comes from .env (APP_DB_PASSWORD), never from the repo.
return new class extends Migration
{
    public function up(): void
    {
        $user = config('database.connections.pgsql.username');
        $password = (string) env('APP_DB_PASSWORD', config('database.connections.pgsql.password'));
        $database = DB::selectOne('select current_database() as name')->name;

        if ($user !== 'app_user' || $password === '') {
            throw new RuntimeException('Set DB_USERNAME=app_user and DB_PASSWORD (or APP_DB_PASSWORD) first.');
        }

        // Skipped when the role exists, so running it twice is safe.
        $exists = DB::selectOne('select 1 as x from pg_roles where rolname = ?', [$user]);
        if (! $exists) {
            DB::statement('CREATE ROLE app_user LOGIN NOINHERIT PASSWORD '.DB::getPdo()->quote($password));
        }

        DB::statement('GRANT CONNECT ON DATABASE "'.str_replace('"', '""', $database).'" TO app_user');
        DB::statement('GRANT USAGE ON SCHEMA public TO app_user');
        DB::statement('GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO app_user');
        DB::statement('GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO app_user');
        DB::statement('ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO app_user');
        DB::statement('ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT USAGE, SELECT ON SEQUENCES TO app_user');
    }

    // The role is kept on rollback: dropping it would lock the running app out.
    public function down(): void
    {
    }
};
