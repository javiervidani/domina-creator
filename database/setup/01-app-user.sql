-- One-time setup, run as the PostgreSQL superuser on the server, against the app database:
--   sudo -u postgres psql -d DBNAME -v app_pw='LONG_PASSWORD' -v admin_user='PLESK_DB_USER' -f database/setup/01-app-user.sql
-- The password comes from -v so it never lands in the repo. Use the same password as DB_PASSWORD in the server .env.
-- Everything else (grants on public, default privileges) is done by the first migration, run as the admin user.

-- app_user is the only login the application uses. NOINHERIT: it sees no creator data until it does SET ROLE.
CREATE ROLE app_user LOGIN NOINHERIT PASSWORD :'app_pw';

-- Step 2 provisioning creates creator_<id> roles as the admin user, which needs CREATEROLE.
ALTER ROLE :"admin_user" CREATEROLE;
