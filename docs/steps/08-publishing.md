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
