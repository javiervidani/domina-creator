-- One-time setup. Run as the database owner (the admin role Plesk made), connected to the new database:
--   psql -h HOST -U ADMIN_USER -d DBNAME -v app_pw='CHOOSE_A_LONG_PASSWORD' -f database/setup/01-app-user.sql
-- The password comes from -v so it never lands in the repo. Put the same password in the server .env (DB_PASSWORD).

-- app_user is the only login the application uses. NOINHERIT: it sees no creator data until it does SET ROLE.
CREATE ROLE app_user LOGIN NOINHERIT PASSWORD :'app_pw';

GRANT CONNECT ON DATABASE :"DBNAME" TO app_user;

-- Central tables live in public and are always written as public.<table>, so app_user needs rights there.
GRANT USAGE ON SCHEMA public TO app_user;
ALTER DEFAULT PRIVILEGES IN SCHEMA public
    GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO app_user;
ALTER DEFAULT PRIVILEGES IN SCHEMA public
    GRANT USAGE, SELECT ON SEQUENCES TO app_user;

-- Creator roles are created later by the provisioning job (step 2), which needs CREATEROLE.
-- Remove this line if the admin role already has it; PostgreSQL 16+ also needs ADMIN on each new role, checked in step 2.
ALTER ROLE CURRENT_USER CREATEROLE;
