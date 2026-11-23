# Step 13: CSV export and backups (last phase)

**Status: built 2026-11-22 in two parts (export, backups). Not yet tried live. Day-60 export link, Google Drive and Box are not built.**

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

## Future, asked by the owner (2026-11-18): media links in the export
- The CSV export includes the media and thumbnail addresses as text columns. There are no media files to export or back up, because none are stored.

## Built (2026-11-22), checked on a throwaway local PostgreSQL 16 with the same roles as Plesk
- **Export my data** (Settings > Data, behind the password, 3 a minute): one zip of plain CSV files, one per table of the creator's schema, UTF-8 with a BOM so Excel reads Hebrew, plus a README. Secrets are left out by table name (`account_tokens`, `ai_keys`, `feed_tokens`); anything new added to the schema is exported by default. Checked: Hebrew, commas and line breaks in texts, empty tables keep their header, no secret value in any file. `app/Export/CreatorExport`, `ExportController`.
- **Backups (nightly run PAUSED 2026-11-23: set `BACKUPS_ENABLED=true` in `.env` to switch it on; the commands still work by hand):** `creators:backup` (daily 02:30, before the purges) runs `pg_dump -Fc` of each active creator schema as `app_user --role=creator_<id>` (no admin login), encrypts it with AES-256-GCM using a key derived per creator from `APP_KEY`, and stores it as `storage/app/private/backups/<id>/<date>.dump.enc`; the newest 7 are kept (`--keep=N`). **If `APP_KEY` is lost the backups cannot be read: keep a copy of it somewhere safe, outside the server.** Backups of a creator are deleted at day 60 with the rest of their history.
- **Restore:** `creators:restore <creator id> <file>` decrypts, makes an empty schema with `public.provision_creator()` and restores into it with `pg_restore --role`. It refuses when the schema still has tables (drop the damaged one first, on purpose) and refuses a wrong creator id or key. Checked: drop the schema, restore, the post, its flags and the settings are back. It restores a live creator whose schema was damaged; a creator purged at day 60 is not restorable (their central rows are gone).
- Needs `pg_dump` and `pg_restore` on the server (set `PG_DUMP_BINARY` and `PG_RESTORE_BINARY` in `.env` if they are not on the PATH, and use the same major version as the server). Not yet checked on Plesk.

## Not built
- The export kept 15 days behind a signed link at day 60: it needs the creator's email, and mail is not set up. Until then the creator is told to export before deleting (said on the Data tab).
- Copies to Google Drive or Box with the creator's own OAuth, and backups kept off the server. Today backups sit on the same disk as the site, which protects against a damaged schema but not against losing the server: copy `storage/app/private/backups` elsewhere or use Plesk's own backup.
