# Actions list (2026-11-24)

Who: **Owner** = needs you (server, accounts, live tests). **Claude** = code or docs I can do when you say. Tick as you go.

## 1. Server and settings (Owner)
- [ ] Cron `schedule:run` every minute, and a queue worker running. Without them sync, publishing, AI, transcripts and picture copies wait.
- [ ] `.env`: `APP_NAME=domina-creator`, `MAIL_*` (password reset, later notification emails), `PG_DUMP_BINARY` and `PG_RESTORE_BINARY` if not on the PATH.
- [ ] Keep a safe copy of `APP_KEY` outside the server (encrypted data and backups cannot be read without it).
- [ ] Rotate the ops token (it was shared in chat). Record the deployed commit so `/ops/health` stops saying `unknown`.
- [ ] Remove the temporary Instagram token form (you said you would). Do not leave it once other creators connect.
- [ ] Backups are paused. When ready: set `BACKUPS_ENABLED=true`, run `creators:backup` once by hand, check a file appears, and copy `storage/app/private/backups` off the server (or use Plesk backup).
- [ ] When the work is merged, switch the Plesk Git branch back to `main`.
- [ ] Run `php artisan test` and Pint on a machine that can install the dev packages and paste the failures (never run so far).
- [ ] Privacy page: review the draft and add your contact email.

## 2. Accounts and approvals (Owner)
- [ ] Meta approval so other creators can connect Instagram (today only your account works).
- [ ] Google verification for YouTube so other creators can connect. Until then the sign-in expires about every 7 days (reconnect) and only listed test users can connect.
- [ ] Telegram: keep the test channel. After testing, put the target back to **Review first**.

## 3. Live checks still open (Owner)
- [ ] Telegram **Auto** mode: new Instagram post, Sync now, it is sent without approval (test channel only).
- [ ] Step 3: password reset email (needs `MAIL_*`).
- [ ] Step 5: the scheduled pull and a real `source_changed` (edit a post here, then change it on Instagram).
- [ ] Step 7: autosave after reload, History, Combine with sources, Compare.
- [ ] Step 9: the setup wizard from the start (use a second test account).
- [ ] Step 11: put a collection embed on a site of yours. There is no connector for this: paste the iframe code into any page (a WordPress "custom HTML" block works), and add that site under Allowed sites.
- [ ] Step 13: Settings, Data, Export my data: open the zip in Excel and check Hebrew.
- [ ] Step 14: the kept picture also reaches Telegram (it is uploaded, not linked).
- [ ] Step 17: "In my style" Rewrite with a saved guide, compare with it unticked, and check the panel names what was used. Try a larger OpenAI model (Test key, then pick from the list).

## 4. Next features (Claude, when you decide)
- [ ] **Speech to text** (BACKLOG, owner is still thinking about the flow, 2026-11-25). Idea so far: hosted Whisper with the creator's own key; Instagram videos sent by their link (short reels fit about 25 MB); YouTube videos with no captions by a "Transcribe a file" upload (no downloading from YouTube); audio never stored; the result lands in the Transcript card.
- [ ] **Style retrieval** (step 17 part 3): examples picked by topic with pgvector. Only worth it with a large library. Needs the extension check described in `docs/steps/17-my-style.md`.
- [ ] **Email**: send the notification emails (choices already saved) and the day-60 export link. Needs `MAIL_*` first.
- [ ] **Team invites** with roles (Settings, Team says "later").
- [ ] **Keep Telegram copies working when the bot or chat changes** (today the old copy says "not connected any more"; Remove is the way out).
- [ ] Publishing to YouTube (update title or description of your own videos) and other networks.
- [ ] Carousels: all pictures, not only the first.
- [ ] Optional: S3-compatible storage for kept pictures, Google Drive or Box copies of backups.
- [ ] **A book from the creator's content** (idea 2026-11-25, see `docs/steps/18-book-from-content.md`, concept only).
- [ ] Tag removal and a tag filter in the Library (offered earlier, not requested).

## 5. Rules to keep in mind
- Never put tokens, keys or real creator data in the repo, logs or tests.
- Deleting a workspace: export first. After the request the workspace is locked for 60 days, then erased with its pictures and backups.
- AI only suggests. Nothing is applied or published without a click.
