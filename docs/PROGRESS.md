# Progress (high level)

Update this file at the end of every step. Mark a step Done only when it runs on Plesk or in tests.

## Status
| # | Step | State |
|---|---|---|
| 0 | Planning, mockups, decisions | Done |
| 1 | Project setup and deploy to Plesk | Done |
| 2 | Database, roles, creator schemas | Code written, not yet run on Plesk |
| 3 | Sign in and registration | Code written, not yet run on Plesk |
| 4 | Accounts and connections | Code written, not yet run on Plesk |
| 5 | Import and the one ID | Not started |
| 6 | Library page | Not started |
| 7 | Content editor | Not started |
| 8 | Publishing and re-publish | Not started |
| 9 | Dashboard and onboarding | Not started |
| 10 | Settings and AI keys | Not started |
| 11 | Collections and website embed | Not started |
| 12 | Deletion and 60-day recovery | Not started |
| 13 | CSV export and backups (last) | Not started |

## Done so far
- Mockups of the Library and the Content editor, including the AI suggestions panel.
- Decisions: Laravel on Plesk, PostgreSQL, schema per creator, no Redis, database queue, review-first publishing,
  manual re-publish, 60-day recovery.
- Repo `domina-creator` created and documented.
- Deploy flow tested: push to `main` reaches the Plesk domain through the webhook.
- Laravel 13 skeleton pushed; read-only ops API (`/ops/health`, `/ops/logs`) added. Laravel runs on Plesk (PHP 8.3, home page loads). PostgreSQL database `ds_domina_creator` is live: `app_user` (NOINHERIT, no CREATEROLE) is the app login, the Plesk DB user is admin with CREATEROLE, central tables migrated with `migrate --database=pgsql_admin --force`.

- Step 2 code: own `Tenancy` class, `public.provision_creator(id)` SQL function (SECURITY DEFINER) creates the role and schema on demand, `creators:migrate` command, `CreatorJob` base job, tenancy tests (PostgreSQL only). Central migrations moved to `database/migrations/central`; session, cache and queue tables are qualified as `public.*`.
- Step 3 code: register (creates user, creator, member, provisions the schema), sign in/out with throttling, password reset, `tenant` middleware (enters the schema after `auth`, leaves before the session is saved), empty Library page. Bootstrap 5.3.3 vendored in `public/vendor/bootstrap`; own styles in `public/css/app.css`. Password reset needs `MAIL_*` set in the server `.env`.
- Step 4 code: Accounts page (health badge, import on/off, frequency, Sync now, source of truth with confirm, targets with review-first default, activity list), Instagram connect through our own Socialite provider for Instagram Login (`app/Platforms/Instagram`), encrypted tokens, `RefreshTokenJob` and `accounts:refresh-tokens` (weekly in the scheduler), `SyncAccountJob` (reads one page and records the run; storing content is step 5). `laravel/socialite` ^5.33 added to `composer.json` (supports Laravel 13); the community Instagram provider is not used because it targets the retired Basic Display API.
- UI references recorded in `docs/UI.md` (mockups for the Library and the Content editor).
- `docs/missing/` lists the missing elements for steps 2 to 4. The Instagram connection waits on Meta (the app, then approval for other creators).

## Open questions
- PHP on the Plesk domain is 8.3.36 with pdo_pgsql, mbstring, openssl, zip and the other Laravel extensions: Laravel 12 or newer fits.
- Plesk webhook works. The deploy folder is the web root today; Laravel needs the document root set to `public`.
- Sign-in library: decided, plain Laravel auth with our own controllers (no package). Easy to swap for Fortify later.
- Tenancy: decided, our own small class. Role and schema are created on demand by a SQL function.
- Repo name was confirmed as `domina-creator`. Domain name is not decided.

## Next
Deploy: paste `bash deploy/post-deploy.sh >> storage/logs/deploy.log 2>&1` into Plesk Git, Deployment settings, Post deployment actions. It runs Composer install, both migrations, and the Laravel caches on every push to `main`. This replaces the manual Package Dependencies step (`composer.lock` now includes Socialite).
Owner checklist for steps 2 to 4: (1) `composer.lock` is updated, nothing to do; (2) `migrate --database=pgsql_admin --force`; (3) set `INSTAGRAM_CLIENT_ID`, `INSTAGRAM_CLIENT_SECRET`, `INSTAGRAM_REDIRECT_URI` and `MAIL_*` in `.env`; add the redirect URI in the Meta app; (4) cron `schedule:run` every minute and the queue; (5) run `php artisan test` and the PostgreSQL tests, paste failures. Step 2 detail: owner runs `migrate --database=pgsql_admin --force`, then the TenancyTest on PostgreSQL, and pastes results. See `docs/steps/02-database-and-tenancy.md`.
Open for step 1 housekeeping: rotate the ops token, record the deployed commit in `storage/app/deploy-commit`.

## Blockers
- Claude's workspace cannot reach `repo.packagist.org` (egress policy), so Composer cannot install Laravel there.
  The owner must allow `repo.packagist.org` and `packagist.org` in the Claude settings (Capabilities, allowed domains).
