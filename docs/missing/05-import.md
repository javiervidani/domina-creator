# Step 5 missing: import and the one ID

## Done
- Scheduled pull (`accounts:sync-due`, every 5 minutes, by each account's frequency), queued with 3 tries, run record.
- Contents and copies, duplicate linking across accounts, `source_changed` without overwriting edits.

## Proven live (2026-11-18)
- Sync now imported 25 posts, a second run created 0, the newest copy is the real latest post.

## Missing
- [ ] **Owner:** tick Import on the account and wait 5 to 10 minutes to see the scheduler start a run by itself.
- [ ] **Owner:** to see `source_changed` live: edit a caption on Instagram, edit the same content's master text in the database, run Sync now.
- [ ] **Owner:** queue worker and scheduler cron must run (see `04-accounts.md`), then run **Sync now** and check the Activity row.
- [ ] **Claude:** tests have never run (see the step 2 file).
- [ ] **Claude:** later runs read at most 4 pages and stop at a page older than a week before the last run, so an edit to an old post is not noticed. Decide with the owner if a full re-check is wanted (for example weekly).
- [ ] **Claude:** captions of carousel and video posts import as text only; media is ignored by design in phase 1.
- [ ] **Later (step 6):** show the content in the Library, and let the creator confirm or undo an automatic link.
- [ ] **Later (step 7):** Keep mine, Take the new version, Compare, and content versions.
- [ ] **Later:** import from YouTube and Telegram (only the Instagram connector exists).
