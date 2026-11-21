# Step 14 (backlog, last): optional light image storage

**Status: built 2026-11-21 (owner asked to do it before step 13 and step 15), not yet tried live.** Added 2026-11-18.

**Why:** phase 1 shows and sends media by link only. The network's addresses expire, so older posts lose their picture and
a send can fail on an old link. A light copy of the image would keep it working without storing heavy files.

## Idea (the "clever" way to save light images)
- Store a **small optimized copy of images only** (resized to a few hundred to about 1200 px wide, WebP or JPEG, a hard size
  cap per image), never the original file and never video. For a video keep only its thumbnail as a light image.
- Make it **opt-in** (a switch in Settings, per account or global) and **lazy**: make the copy when the link is about to
  expire or when the creator publishes, not for every post at import.
- Fall back to the link when no copy exists. The editor keeps saying whether a picture is "linked" or "kept".

## Do
- A light-copy job per image (queued, carries the creator id, switches schema like every job), with retries.
- Storage on the server disk (later optional S3), one folder per creator, so deleting a creator removes it with the
  60-day purge (step 12). A per-creator size budget with a clear message when it is full.
- Show kept versus linked in the editor's Media card. Use the copy when sending to Telegram or other networks if the link is dead.
- Candidate packages: `intervention/image` for resizing, `spatie/laravel-medialibrary` if conversions are wanted. Check
  version and Laravel compatibility before installing.

## Done when
- With the switch on, a post's picture still shows after its original link has expired.
- With the switch off, nothing is stored and behaviour is unchanged.
- Deleting a creator's history removes their light copies. The export lists them as file names, not as files.

## Notes
- Related: `docs/steps/05-import.md` (media links), `docs/steps/08-publishing.md` (sending media by link),
  `docs/steps/12-deletion-recovery.md`, `docs/steps/13-export-and-backup.md`.

## Built (2026-11-21), minimum disk
- **Opt-in** switch in Settings > Pictures (`keep_images`, off by default). Delete kept pictures button; switching off keeps what is there.
- **What is stored:** only the post's picture (a video's cover), shrunk to 640 px on the long side as WebP quality 70: a 455 KB photo became 37 KB in the check. Never the original, never a video, a carousel keeps its first picture. Downloads over 6 MB are skipped.
- **When:** right after the import, while Instagram's address still works (`KeepImageJob`, queued from `SyncAccountJob`, only for new posts). Older posts stay linked: their addresses are already dead or will be. No backfill.
- **Where:** the private local disk, `storage/app/private/creators/<creator id>/<copy id>.webp`. Shown to the signed-in creator only (`/media/{copy}`), never public. Budget 100 MB per creator; when full, new pictures stay links.
- **Used for:** the editor's Media card (says "Small copy kept") and Telegram: a kept copy is uploaded, so it never expires; without one Telegram downloads the link as before.
- **Removal:** with the post (`items:purge` sweeps files whose post is gone) and with the whole history at day 60 (`CreatorDeletion`).
- Code: `app/Media/ImageKeeper`, `KeepImageJob`, `MediaController`, `PicturesController`, migration `2026_11_21_000001` (`kept_path`, `kept_bytes`). Needs PHP GD with WebP (present). S3 is not built.
