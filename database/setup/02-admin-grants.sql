-- One-time setup, run as the PostgreSQL superuser, against the app database:
--   sudo -u postgres psql -d DBNAME -v admin_user='PLESK_DB_USER' -f database/setup/02-admin-grants.sql
-- public.provision_creator() runs as the admin user and creates one schema per creator. That needs CREATE on the
-- database, which a Plesk DB user does not always have. The admin user already has CREATEROLE (see 01-app-user.sql).
SELECT format('GRANT CREATE ON DATABASE %I TO %I', current_database(), :'admin_user') \gexec
