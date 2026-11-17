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
