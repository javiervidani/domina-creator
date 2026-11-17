# Handoff for a new chat

Read in this order: `CLAUDE.md`, `PLATFORM.md`, `docs/PROGRESS.md`, `docs/DATABASE.md`, then the current file in `docs/steps/`.
Work happens on Plesk, not on the owner's computer. Step 1 is done. The next step is step 2 (`docs/steps/02-database-and-tenancy.md`).

## How the owner runs things on Plesk
- Server folder: `/var/www/vhosts/datashellv.com/domina-creator.datashellv.com`. Site URL: `https://domina-creator.datashellv.com`.
- The root Terminal's default `php` is 8.2 and fails. Always use `/opt/plesk/php/8.3/bin/php artisan ...`.
- The Toolkit Artisan tab takes one command at a time (no `&&`). In production `migrate` needs `--force`.
- Migrations run as the admin login: `migrate --database=pgsql_admin --force`. The app runs as `app_user`.
- Tests: the owner runs `/opt/plesk/php/8.3/bin/php artisan test` in the Terminal and pastes failures. Claude's workspace cannot install Composer packages.
- Deploy: push to `main`, the webhook updates the folder. It does not run Composer: Package Dependencies, Install.

## Database facts
- Database `ds_domina_creator`. Roles: `app_user` (login, NOINHERIT, no CREATEROLE) and the Plesk DB user (admin, CREATEROLE).
- `app_user` was created by hand with `database/setup/01-app-user.sql` as the PostgreSQL superuser. The Plesk DB user cannot create roles.
- Central tables are migrated. No creator schemas exist yet.

## Reading production errors
`GET /ops/health` and `GET /ops/logs?level=error&limit=10`, token in `OPS_TOKEN` (`?k=` or Bearer). Never write the token in the repo or in docs.

## Open items
- Rotate the ops token (it was shared in chat). Set `APP_NAME=domina-creator` in the server `.env`.
- `/ops/health` shows commit `unknown`: write the deployed commit id to `storage/app/deploy-commit` in the deploy step.
- Tenancy: a package or our own small class (decide with the owner before step 2 code).
- Sign-in library: Fortify or the official starter kit (step 3).
