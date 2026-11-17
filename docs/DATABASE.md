# Database

Engine: PostgreSQL, one new database. Every creator has a private schema. Decisions below came from the planning chat.

## Naming
- Schema and owner role are both `creator_<id>` (for example `creator_1234`). The id comes from `public.creators`.
- The central schema is `public`.

## Roles and access
- **`creator_<id>` role.** Owns the schema `creator_<id>`. It is `NOLOGIN`: nobody can connect as it.
- **`app_user` role.** The one login the application uses. Member of every creator role, set to `NOINHERIT`,
  so on its own it sees no creator data.
- **Per request and per queue job:** `SET ROLE creator_<id>` and `SET search_path = creator_<id>`.
  A request that forgets to switch gets "permission denied", never another creator's data.
- **`public` is not in the search path.** Creator tables are written unqualified (`contents`). Central tables are
  always qualified (`public.users`). In Laravel a central model sets `$table = 'public.users'`.
- A creator role may read only the reference tables it needs from `public` (for example plan limits). It cannot read
  `users`, `creators`, or any other creator's schema.
- **No foreign keys** from creator tables to `public` tables. User ids are stored as plain numbers, so a schema can be
  dropped or exported without touching `public`.
- **Provisioning** is done by the SQL function `public.provision_creator(bigint)`, `SECURITY DEFINER`, owned by the admin
  login (CREATEROLE). It creates the role and schema when a creator first needs them and grants the role to `app_user`.
  Only `app_user` may run it. The creator migrations then run as that creator role, so no admin login is used at runtime.

## Central schema (`public`)
- users, creators (one per workspace), creator_members (user, creator, role)
- tenants: creator id, schema name, status (`active`, `pending_deletion`, `deleted`), purge date
- deletion_requests, plans, subscriptions (later)
- sessions, jobs, failed_jobs, cache

## Per-creator schema (`creator_<id>`)
**Accounts and sync**
- connected_accounts: platform, handle, status, is_source_of_truth, import_enabled, import_frequency
- account_tokens: encrypted access and refresh tokens, expiry
- account_targets: source account, target account, mode (`auto` or `review`), what to send
- sync_runs: each pull, its result and errors

**Content**
- contents: the one platform ID, title, master text, status, deleted_at
- content_parts: the sources that make up a piece, in order, each linked to its origin
- content_copies: the linked copy on each platform: external id, status, last published text hash
- content_target_texts: an edited version for one target
- content_versions: edit history
- tags, content_tag

**Publishing**
- publish_jobs: requested or scheduled publishes and their result

**Collections and embeds (later)**
- collections, collection_items, embed_settings, feed_tokens

**AI (phase 2)**
- ai_keys: provider, encrypted key, default model
- ai_suggestions: offered text, applied or dismissed

**Logs**
- activity_log, notifications

## Content model rules
- One content ID. A copy of the same content on another network attaches to the existing ID.
- Duplicate detection runs on import and links a match to the existing ID. The creator can confirm or undo it.
- Originals are never edited. Edits live on the platform copy and in `content_target_texts`.
- If the source changes after import, the item is flagged `source_changed`. The creator picks Keep mine, Take the new
  version, or Compare.
- Re-publish is always a manual action.

## Deleting a creator's history (60-day recovery)
1. The owner confirms. `tenants.status` becomes `pending_deletion` with a purge date 60 days ahead.
2. Sync and publish jobs stop. Connected accounts are disconnected and tokens revoked.
3. The schema is locked by revoking `app_user`'s membership in `creator_<id>`. Nothing is dropped.
4. During the 60 days the owner can restore: membership is granted back and the creator reconnects accounts.
5. On day 60 a scheduled job drops the schema and the role, removes the creator's central rows, and keeps a minimal
   record that the deletion happened.
6. Single items (a post, a collection) use `deleted_at` and a 60-day purge inside the schema.

## Export and backup (last phase)
- **CSV export:** at day 60 a zip of plain CSV files, one per table, secrets excluded (tokens, AI keys). It is kept
  15 more days behind an expiring signed link, then deleted.
- **Backups:** the whole database is backed up as usual. Per creator, `pg_dump --schema=creator_<id> -Fc`, encrypted
  with a per-creator key before upload. Destinations: local, plus optional Google Drive or Box that the creator
  connects. A creator's dumps on our storage are deleted at day 60. Needs `pg_dump` on the Plesk server.
- Deleted data survives in older backups until they rotate out. State the retention in the privacy policy.

## Open items
- Exact column lists. They are written step by step in `docs/steps/`.
