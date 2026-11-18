# Step 8 missing: publishing and re-publish

## Done
- Telegram connect, drafts, Approve and publish, Re-publish (edit in place), Retry, differs note, retries for temporary failures.

## Missing
- [ ] **Owner:** try it live. Make a bot with @BotFather, add it as admin of a test channel, **Connect Telegram** on Accounts (bot token and `@channel`), add Instagram -> Telegram as a target (stays Review first), open a content, **Create a draft for Telegram**, **Approve and publish**, edit the text, then **Re-publish**.
- [ ] **Owner:** the queue worker must be running (publishing is a queued job).
- [ ] **Claude:** tests have never run (see the step 2 file).
- [ ] **Later:** YouTube publishing (needs the video, the YouTube connector, quota handling and the Reels audio warning). The step file lists it; it cannot be text only.
- [ ] **Claude (ask first):** backfill drafts for old contents when a target is added; a place to see the send log (`publish_jobs` is recorded but not shown).
- [ ] **Claude:** the Compare/Take-new-version actions change only the master text; whether a Telegram copy should be flagged then is not decided.
