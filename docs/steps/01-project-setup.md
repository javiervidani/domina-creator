# Step 1: Project setup and deploy

**Goal:** an empty Laravel app opens on Plesk and redeploys on every push to `main`.

## Do
- Create the Laravel project in the repo root (version chosen from the Plesk PHP version).
- Set `.env.example` for PostgreSQL, `database` queue, `database` cache and sessions.
- Tests use PHPUnit (the skeleton default) and Pint. Larastan comes later.
- Connect the GitHub webhook to the Plesk Git extension (owner does this in Plesk and GitHub).
- Plesk deploy does not run Composer: Laravel Toolkit, Package Dependencies, Install. Then create `.env` from `.env.example`, run `key:generate`, and `migrate --force` in the Artisan tab.
- Add the Plesk cron entries for `schedule:run` and `queue:work --stop-when-empty`.

## Done when
- The home page loads on the Plesk domain and a push to `main` updates it.
- A failed deploy is visible in the Plesk Git log.

## Needs from the owner
- PHP version, the domain, the PostgreSQL database and `app_user` credentials, webhook set up.

## Ops API (read-only, for Claude)
Claude cannot read Plesk logs, so two token-protected GET endpoints give a short plain-text view.
- Set `OPS_TOKEN` in `.env` (32+ random characters). Optional `OPS_TOKEN_EXPIRES=YYYY-MM-DD` (UTC, end of day).
- Send the token as `Authorization: Bearer <token>` or `?k=<token>`. Any failure is a plain 404. Rotate it when the `?k=` form was used (it appears in access logs).
- `/ops/health`: app, PHP and Laravel versions, commit, database check, log size, last error.
- `/ops/logs?level=error&limit=10&since=2026-11-17 14:00`: one line per distinct problem with a count, newest first. Secrets are masked and lines cut.
- Code is in `app/Ops`; config in `config/ops.php`. Rate limit uses the file cache (`CACHE_LIMITER`), so it works when the database is down.
