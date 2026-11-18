# Step 8: Publishing and re-publish

**Goal:** send content to targets, and update a published copy only when the creator asks.

## Do
- A publish job per target (Telegram first, then YouTube). Retries and quota handling.
- Review-first targets create drafts. Approve and publish sends them. Auto targets publish on import.
- Re-publish is a manual button per copy and never runs by itself.
- Record external ids and a hash of the published text, so the editor shows when a copy differs.
- Warn about Reels audio that may not be cleared for YouTube.

## Done when
- A draft can be approved, a published copy can be re-published, and failures show with a retry.

## Built
- `app/Publishing` (Publisher, TargetPlanner, TargetText), `app/Platforms/Telegram/TelegramConnector`, `app/Jobs/PublishCopyJob`, controllers `PublishingController`, `TelegramConnectController`.
- Telegram bot token and chat go in the **Connect Telegram** form on Accounts. The bot must be an admin of the channel.
- A target copy is a `content_copies` row (`link_mode target`, status draft, queued, published, failed). The text sent is the Telegram tab text if the creator wrote one, otherwise the master text. Over 4096 characters is refused with a message, never cut.
- Re-publish edits the same Telegram message (`editMessageText`), so no second post. It is a button only.
- New contents from an Instagram sync get a draft per target; Auto targets are queued right away. Adding a target later does not backfill old contents: use "Create a draft for Telegram" in the editor.
- Failures: the copy shows Failed with Telegram's reason and a Retry button. Rate limits and Telegram outages retry by themselves (3 tries).
- Not built: YouTube publishing and the Reels-audio warning (YouTube needs a video, phase 1 is text only), per-target scheduling.
- Tests: `TelegramConnectorTest`, `PublisherDiffersTest`, `PublishingTest` (PostgreSQL only). Never run yet.
