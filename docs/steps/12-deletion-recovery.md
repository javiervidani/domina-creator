# Step 12: Deletion and 60-day recovery

**Goal:** a creator can delete their history and restore it for 60 days.

## Do
- Owner confirmation in Settings, then `pending_deletion` with a purge date.
- Stop jobs, disconnect accounts, revoke tokens, lock the schema (revoke `app_user` membership).
- Restore action: grant the membership back, creator reconnects accounts.
- Daily job: at day 60 drop the schema and role, delete central rows, keep a minimal record.
- Item-level `deleted_at` with a 60-day purge (Recycle bin).

## Done when
- Lock, restore, and purge are tested, and a locked schema cannot be entered by the app.

## Built
- `app/Tenancy/CreatorDeletion` (request, restore, purgeDue), `DeletionController`, `RecycleBinController`, commands `creators:purge` and `items:purge` (scheduled daily at 03:25 and 03:10), models `DeletionRequest`.
- Central migration `2026_11_19_000001`: `public.lock_creator`, `unlock_creator`, `purge_creator`. Run it with the admin login like the first function (the deploy script already does). Each function checks `public.tenants` itself, so the app cannot lock or drop a creator that is not being deleted, and cannot purge before day 60.
- Request: owner confirms in Settings > Data with the password and the word DELETE. Tokens are removed, accounts marked reconnect, feed links revoked, tenant `pending_deletion` with a purge date 60 days out, then the schema is locked by revoking `app_user`'s membership. Nothing is dropped. Locked creators are redirected to a pending page (outside the workspace) that offers Restore. Queued jobs skip a locked creator.
- Restore: membership granted back, status active. Accounts must be reconnected (tokens were removed) and collection links recreated (feed tokens stay revoked).
- Purge at day 60: schema and role dropped, central rows and the user and sessions removed, `deletion_requests` kept as the minimal record (ids and dates, no name or email).
- Recycle bin: deleting a post or a collection sets `deleted_at` (collections gained `deleted_at`); restore or delete for good; the nightly job removes items after 60 days. A post deleted for good that is still on its network can return as a new item on a later sync; a post still in the bin is not recreated or changed by a sync.
- Tests: `DeletionTest` (PostgreSQL only).
