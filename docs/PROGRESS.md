# Progress (high level)

Update this file at the end of every step. Mark a step Done only when it runs on Plesk or in tests.

## Status
| # | Step | State |
|---|---|---|
| 0 | Planning, mockups, decisions | Done |
| 1 | Project setup and deploy to Plesk | Done |
| 2 | Database, roles, creator schemas | Running on Plesk (schema made on sign-up); isolation tests not run yet |
| 3 | Sign in and registration | Running on Plesk; password reset (mail) not tried; tests not run yet |
| 4 | Accounts and connections | Running on Plesk with a pasted token; Instagram sign-in waits on Meta; tests not run yet |
| 5 | Import and the one ID | Import and no-duplicates proven live (25 posts, 2nd run 0 new); scheduled pull and source_changed not yet seen live |
| 6 | Library page | Library page and content page work live (owner, 2026-11-18). Combine opens a minimal editable page (title and text); the full editor is step 7. Editable page not yet tried live; PHPUnit suites not run |
| 7 | Content editor | Works live (owner screenshots, 2026-11-18): editor opens with Hebrew, Master/YouTube/Telegram tabs, limit and count, Linked copies. Not yet confirmed live: autosave after reload, History, Combine with sources, Compare. PHPUnit not run |
| 8 | Publishing and re-publish | Works live (owner, 2026-11-21): Review first, Re-publish, picture option |
| 9 | Dashboard and onboarding | Code written, not yet tried live |
| 10 | Settings and AI keys | Code written, not yet tried live |
| 11 | Collections and website embed | Code written; not yet tried live (the owner has not embedded it on another site; there is no connector for that) |
| 12 | Deletion and 60-day recovery | Works live (owner, 2026-11-21); also checked locally on PostgreSQL 16 |
| 13 | CSV export and backups | Postponed to the end (owner, 2026-11-21) |
| 14 | Optional light image storage | Built 2026-11-21 (small WebP copies, opt-in, 100 MB per creator); not yet tried live |
| 15 | AI suggestions: rewrite and shorten per post (phase 2) | Built 2026-11-22; not yet tried live |

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
- Step 5 code: `contents` and `content_copies` tables, `app/Import` (CaptionText, ContentImporter, AccountImporter, ImportSchedule), `SyncAccountJob` now imports (3 tries), `accounts:sync-due` every 5 minutes. Text only. Run `creators:migrate` (the deploy script does).
- Step 5 proven live on the owner's account: 25 posts imported, a second run created none, the newest post matches the real latest post. Hebrew text stored correctly.
- Step 6 code: Library list (search, account, status, source-changed filters, Needs review view, pagination), selecting rows with Combine and Tag, read-only content page, mockup top bar with sync chip dropdown and drafts badge. New tables `content_parts`, `tags`, `content_tag`.
- 2026-11-18: ran the real app against a throwaway local PostgreSQL 16 (same roles as Plesk), one HTTP request at a time: register provisions a schema; import of 25 posts creates 25, a second run creates 0, an edited original keeps the creator's text and sets `source_changed`, the same text on another account links to the existing content; Library lists 25 per page with Hebrew right to left, search (English and Hebrew), source-changed, Needs review, status filters, Combine (2 items make a draft with 2 parts, 1 item is refused), Tag, drafts badge; a creator cannot read another creator's schema or `public.users`, and `app_user` without `SET ROLE` is denied. The PHPUnit suites themselves still have not run (dev dependencies cannot be installed here).
- 2026-11-18: owner confirmed the Library page works live ("working great") with the 25 imported posts. Whether the owner tried Combine and Tag live was not stated.
- 2026-11-18: owner confirmed `/content/3` opens live. Then, with the owner's "no preference" on the editor question, the content page became a minimal editor (title and text, Save) so Combine opens something editable. Not yet tried live.
- Step 7 code: editor page at `/content/{id}` (Sources list with add/reorder/remove/rebuild, Master/YouTube/Telegram tabs with limits and "same as master", autosave, source-changed banner with Keep mine / Take the new version / Compare, Linked copies and History). New tables `content_target_texts`, `content_versions`. The compare view is our own small word diff (`app/Content/TextDiff`), not the proposed `jfcherng/php-diff`.
- 2026-11-18: owner's phone screenshots show the live Library (25 posts, Hebrew right to left, IG/YT/TG icons, Imported status) and the editor (CNT-1, Sources, tabs, "Same as master" with "910 characters, YouTube limit 5,000", Linked copies). The two new tables exist on the server. The top bar looked unstyled, probably a cached stylesheet; the stylesheet link now carries a version.
- Step 8 code (Telegram first, as the step file says; YouTube later because it needs a video and phase 1 is text only): `Connect Telegram` on Accounts (bot token + channel, checked and stored encrypted), draft copies for targets (review first by default, Auto queues on import), `PublishCopyJob` (sends, or edits the same message on Re-publish; temporary failures retry), editor's Linked copies panel with Approve and publish / Re-publish / Retry, a note when your text differs from the published copy, hash and time of the last send. New tables/columns: `publish_jobs`, status/published_hash/published_at/last_error on `content_copies`.
- Step 9 code: `/dashboard` (needs-attention list: reconnect or expiring accounts, failed publishes, Telegram drafts, source-changed items; recent imports; the sync line "Instagram @x to Telegram Chan (review)"), `/onboarding` wizard (connect Instagram, source of truth, targets with review first or Auto, first import) that keeps no state of its own, home `/` sends an unfinished creator to the wizard and others to the dashboard, Dashboard tab in the top bar. No new tables.
- Owner decision 2026-11-18: phase 1 now includes **linked media**: the post's image or video is shown in the editor and, later, sent to other networks as a link, never copied. Built: media links stored on import, a Media card in the editor. Recorded for later in the step 8, 11 and 13 files. `CLAUDE.md`, `PLATFORM.md` and `docs/STACK.md` updated.
- Step 10 code: Settings page (Profile and password, Notification choices, AI keys, Team note). AI keys: one row per provider (Claude, OpenAI, Grok, Groq), encrypted, shown as the last four characters only, default model, on/off, default provider, Test key (lists the provider's models, so no text is generated), Remove. New tables `settings`, `ai_keys` in the creator schema. Decided by default because the owner did not answer: team invites later, notification choices saved but no emails sent yet. Backlog step 14 (optional light image storage) added.
- Step 11 code: Collections tab (create, items with reorder and remove, details with cover link and visibility private / link / public, Add to collection from the Library), Website embed tab (layout, accent, show picture, allowed sites, embed code, RSS and JSON links, create a new link). Public read-only pages `/embed/{token}`, `/feed/{token}/json|rss`, `/c/{creator}/{slug}[/json|/rss]` with no session. Own feed tokens (`<creator id>.<40 random>`, encrypted plus hashed), no Sanctum. Frame rules via `Content-Security-Policy: frame-ancestors` from the allowed sites; JSON CORS only for those sites. New tables `collections`, `collection_items`, `embed_settings`, `feed_tokens`.
- Step 12 code: Settings > Data (password and the word DELETE), `CreatorDeletion` (request, restore, purgeDue), SQL functions `lock_creator`, `unlock_creator`, `purge_creator` (admin-owned, each refuses unless `public.tenants` shows the right state, purge only after day 60), pending page with Restore, Recycle bin for posts and collections (`deleted_at`, 60 days), nightly `items:purge` and `creators:purge`, queued jobs skip a locked creator.
- 2026-11-19: checked locally on PostgreSQL 16 with the admin as a non-superuser, one request at a time through the real app: item delete, restore and bin; wrong password and missing word refused; request locks (app_user gets `permission denied` on SET ROLE and on the schema), tokens removed, 60-day purge date, pending page and redirect, restore reopens it, the SQL functions refuse an active creator or a creator not yet due, a purge at day 60 drops schema and role, removes the creator, member, tenant, user and sessions and keeps a minimal `deletion_requests` row, queued jobs are skipped for a locked creator, the nightly item purge removes a 61-day item and keeps a 10-day one. Two bugs found and fixed by this check (purge date not cast, a restored request wrongly stamped as purged). PHPUnit has still not run.
- Combined posts no longer get an English "Combined:" title prefix (existing drafts tidied by a migration); Library has a Drafts filter pill with a count.
- 2026-11-19: removed the placeholder `public/index.html` from the first deploy test. Apache served it before Laravel, so the site's root address showed "Under construction" instead of the app (`/dashboard`, `/login` and the rest were never affected).

- 2026-11-19: Telegram publishing proven live (owner: a Hebrew post arrived in the test channel). Added: clearer Telegram errors, Remove for draft or failed copies, and "Send with the picture" (by link; step 8 file). Not yet tried live. Step 15 file written (AI rewrite and shorten, audio and on-device notes).

## Open questions
- PHP on the Plesk domain is 8.3.36 with pdo_pgsql, mbstring, openssl, zip and the other Laravel extensions: Laravel 12 or newer fits.
- Plesk webhook works. The deploy folder is the web root today; Laravel needs the document root set to `public`.
- Sign-in library: decided, plain Laravel auth with our own controllers (no package). Easy to swap for Fortify later.
- Tenancy: decided, our own small class. Role and schema are created on demand by a SQL function.
- Repo name was confirmed as `domina-creator`. Domain name is not decided.

## Next
Step 7: the Content editor, from the mockup in `docs/UI.md`. Combine and a row click already land on `/content/{id}`, which becomes the editor.
Deploy: paste `bash deploy/post-deploy.sh >> storage/logs/deploy.log 2>&1` into Plesk Git, Deployment settings, Post deployment actions. It runs Composer install, both migrations, and the Laravel caches on every push to `main`. This replaces the manual Package Dependencies step (`composer.lock` now includes Socialite).
Owner checklist for steps 2 to 4: (1) `composer.lock` is updated, nothing to do; (2) `migrate --database=pgsql_admin --force`; (3) set `INSTAGRAM_CLIENT_ID`, `INSTAGRAM_CLIENT_SECRET`, `INSTAGRAM_REDIRECT_URI` and `MAIL_*` in `.env`; add the redirect URI in the Meta app; (4) cron `schedule:run` every minute and the queue; (5) run `php artisan test` and the PostgreSQL tests, paste failures. Step 2 detail: owner runs `migrate --database=pgsql_admin --force`, then the TenancyTest on PostgreSQL, and pastes results. See `docs/steps/02-database-and-tenancy.md`.
Open for step 1 housekeeping: rotate the ops token, record the deployed commit in `storage/app/deploy-commit`.

## Blockers
- Claude's workspace cannot reach `repo.packagist.org` (egress policy), so Composer cannot install Laravel there.
  The owner must allow `repo.packagist.org` and `packagist.org` in the Claude settings (Capabilities, allowed domains).
