# Step 16 (phase 2): YouTube connection and transcripts

**Status: built 2026-11-22 (connect, import, transcript from captions), not yet tried live. Speech to text fallback is not built.** Written 2026-11-22. Owner decision: the YouTube
channel and its transcripts belong to **the creator who connects it** (their own channel, their own sign-in).

**Goal:** a creator connects their YouTube channel, their videos appear in the Library as posts, and for each video the
transcript can be pulled in as editable text (then AI Rewrite and Shorten from step 15 work on it).

## Do
- **Connect:** Google sign-in through Laravel Socialite's Google driver, with our own small provider class if scopes need it
  (`app/Platforms/YouTube`, a `YouTubeConnector` extending `BaseConnector`). Scopes: read-only for the first part
  (`youtube.readonly`); the captions scope (`youtube.force-ssl`) only when the creator asks for transcripts. Tokens are
  encrypted like every other account token; refresh token kept; reconnect flow like Instagram.
- **Import (read-only):** the channel's uploads playlist, newest first; title, description, thumbnail, publish date, link
  to the video. Each video is one Library item with one platform ID (same importer rules: never overwrite an edited text,
  `source_changed`). The thumbnail is a link (or the small kept copy from step 14). The video itself is never downloaded.
- **Transcript:** a "Get transcript" button on a YouTube item. Order of attempts:
  1. The creator's own caption tracks through the YouTube captions API (`captions.list`, `captions.download`), preferring
     the creator's language, then any track.
  2. If there is no downloadable track, offer **speech to text** from audio the creator supplies (see below).
  The transcript is stored as a content part or a draft text the creator edits. Original timestamps are optional.
- **Speech to text fallback (decide before building):** (a) a hosted transcription endpoint using the creator's own key
  (Groq or OpenAI, the same keys as step 15; check file size limits, about 25 MB), or (b) a Whisper model in the browser
  (WebGPU, opt-in, large first download), see step 15 notes. The audio is never stored: it is sent and discarded.
- **Publishing to YouTube** stays out of this step: the API can edit the title and description of the creator's own
  videos (`videos.update`), but cannot post text-only community posts, and uploading needs the video file.

## Rules
- Only the connected creator's own channel and videos. No transcripts of other people's videos, no scraping of
  unofficial transcript endpoints (against YouTube's terms and fragile).
- Transcripts and tokens are creator data: encrypted tokens, schema isolation, removed with the history at day 60.
- The creator sees plainly when a transcript is automatic (can be wrong) and can edit it.

## Needs from the owner (outside the code)
- A Google Cloud project, the YouTube Data API v3 enabled, an OAuth consent screen and an OAuth client (web) with the
  redirect URI of this platform. Values go in `.env` (`GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI`), never in the repo.
- While the app is in Google's "testing" mode the sign-in expires after about 7 days and only listed test users can
  connect. Other creators need Google's verification (like Meta's approval for Instagram).
- Quota: 10,000 units a day. Listing is cheap; `captions.list` 50 and `captions.download` 200 units each, so a bulk transcript run must be limited.

## Open questions (verify on a real video before promising)
- Whether `captions.download` returns automatic (speech-recognised) tracks for the owner's videos, or only uploaded ones.
- Which language tracks exist for the creator's videos (Hebrew automatic captions quality).
- Hosted speech to text versus in-browser Whisper for the fallback, and the file size limits for long videos.

## Done when
- A creator connects a channel and its videos appear in the Library once each, with a link and thumbnail.
- "Get transcript" returns editable text for a video that has captions, and a clear message plus the speech to text option
  for one that has none.
- Disconnecting removes the token; deleting history removes the transcripts.
- Tests with faked Google answers (no real tokens, no real videos).

## Notes
- Related: `docs/steps/04-accounts.md`, `docs/steps/05-import.md`, `docs/steps/15-ai-suggestions.md`, `docs/steps/14-optional-light-image-storage.md`.

## Built (2026-11-22)
- **Connect YouTube** button on Accounts (Google sign-in, own `YouTubeProvider`, read-only `youtube.readonly`). **Allow transcripts** asks again for `youtube.force-ssl`. Offline access: the refresh token is stored (encrypted) and a fresh one-hour access token is fetched before each sync (`YouTubeConnector::forAccount`). `expires_at` stays empty, so the health badge only turns to Reconnect when Google refuses the refresh.
- **Import:** `accounts:sync-due` and Sync now include YouTube. Each public video becomes one Library item: text is the title, a blank line, then the description; the link is the watch page; the thumbnail is a link (the step 14 small copy applies too). Private and deleted videos are skipped. The uploads playlist is derived from the channel id (`UC...` to `UU...`).
- **Transcript:** the editor shows a Transcript card on a YouTube item. "Get transcript from YouTube" picks an uploaded track first (Hebrew first), else an automatic one, converts SRT to plain text with paragraphs at pauses (`Srt`), stores it in `content_transcripts`. "Add below my text" and "Use as my text" write the old text to History first; Remove deletes the transcript. The master text is never changed without a click.
- Targets: the add-target form now lists importing accounts under From and Telegram under To.
- **Owner setup:** Google Cloud project, YouTube Data API v3 on, OAuth consent screen (add yourself as a test user), a Web OAuth client with redirect `https://domina-creator.datashellv.com/accounts/youtube/callback`, then `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI` in the server `.env`.
- **Still open:** whether automatic captions can be downloaded (the message says so if YouTube refuses). Speech to text fallback not built. Migration `2026_11_23_000001`.
