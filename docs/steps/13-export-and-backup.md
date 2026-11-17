# Step 13: CSV export and backups (last phase)

**Goal:** a creator can take their data out, and backups can restore one creator.

## Do
- At day 60: build a zip of plain CSV files (one per table), without tokens and AI keys.
  Keep it 15 more days behind an expiring signed link, then delete it.
- Per-creator `pg_dump --schema=creator_<id> -Fc`, encrypted with a per-creator key.
- Destinations: local storage, plus optional Google Drive or Box through the creator's own OAuth connection.
- Retention policy, a restore command, and deletion of our copies at day 60.
- Requires `pg_dump` on the Plesk server.

## Done when
- A dump of one creator restores into a clean schema, and the export link expires on schedule.
