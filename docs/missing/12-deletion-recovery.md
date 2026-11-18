# Step 12 missing: deletion and 60-day recovery

## Done
- Request, lock, pending page, restore, purge at day 60, minimal record, Recycle bin, nightly purges, jobs stop for a locked creator.

## Missing
- [ ] **Owner:** deploy runs the central migration (new SQL functions) and `creators:migrate` (collections `deleted_at`). Confirm the cron runs `schedule:run` every minute; the purges are scheduled jobs. Then try the Recycle bin on a post. Do NOT try "Delete all my history" on your real workspace unless you mean it: it locks it for 60 days (restorable from the pending page).
- [ ] **Owner:** after a restore, reconnect your accounts (tokens are gone) and create new collection links.
- [ ] **Claude:** PHPUnit has never run (see the step 2 file).
- [ ] **Claude (ask first):** an email to the owner a week before the purge, and when the history is deleted (needs `MAIL_*`; step 10 notifications are not sent yet).
- [ ] **Claude (ask first):** deleting a creator's history does not remove published posts on other networks; a "delete my posts on Telegram" option is not built.
- [ ] **Later (step 13):** the export built at day 60 (CSV zip, 15 more days behind a signed link) is not built yet, so today the purge at day 60 deletes without an export. Build step 13 before relying on the purge for real data.
- [ ] **Later:** the backup rotation and the privacy policy wording for deleted data staying in older backups (`DATABASE.md`).
