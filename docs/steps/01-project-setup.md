# Step 1: Project setup and deploy

**Goal:** an empty Laravel app opens on Plesk and redeploys on every push to `main`.

## Do
- Create the Laravel project in the repo root (version chosen from the Plesk PHP version).
- Set `.env.example` for PostgreSQL, `database` queue, `database` cache and sessions.
- Add Pest, Pint, and Larastan with a passing example test.
- Connect the GitHub webhook to the Plesk Git extension (owner does this in Plesk and GitHub).
- Deploy actions: `composer install --no-dev`, `php artisan migrate --force`, `php artisan config:cache`.
- Add the Plesk cron entries for `schedule:run` and `queue:work --stop-when-empty`.

## Done when
- The home page loads on the Plesk domain and a push to `main` updates it.
- A failed deploy is visible in the Plesk Git log.

## Needs from the owner
- PHP version, the domain, the PostgreSQL database and `app_user` credentials, webhook set up.
