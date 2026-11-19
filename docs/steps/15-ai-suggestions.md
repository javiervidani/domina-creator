# Step 15 (phase 2): AI suggestions, rewrite and shorten per post

**Status: planned, not started. Do not build until the owner says so.** Written 2026-11-19. Phase 1 has no AI
(`CLAUDE.md`); this step starts phase 2. Scope is decided: **rewrite** and **shorten**, **per post** only. Collections are
out of scope.

**Goal:** in the editor, the creator asks AI to rewrite or shorten the text of one tab (master or one target), sees the
suggestion beside the current text, and applies or dismisses it. Nothing changes without a click.

## Already in place (step 10)
- `ai_keys` table and Settings page: provider (Claude, OpenAI, Grok, Groq), encrypted key, default model, on/off, default
  provider. "Test key" lists models only.
- Mockup of the AI suggestions panel in the editor (`docs/UI.md`).

## Do
- Table `ai_suggestions` in the creator schema: content id, tab (master or target), action (`rewrite` or `shorten`),
  input hash, offered text, provider, model, status (`offered`, `applied`, `dismissed`, `failed`), created_at.
  No foreign key to `public`; a plain content id like the other creator tables.
- `app/Ai`: a small base `AiClient` plus one subclass per provider (Claude, OpenAI, Grok, Groq), each with one method
  that takes a prompt and returns text. Plain HTTP with Laravel's client; no SDK package unless the owner agrees.
  Check each provider's current API and model names before coding.
- `SuggestTextJob` (extends `CreatorJob`, queued, `afterResponse` dispatch) so a slow provider never blocks the page.
  The editor polls the suggestion row.
- Prompts: **Rewrite** keeps meaning, facts, links, hashtags and language (Hebrew stays Hebrew, right to left). **Shorten**
  takes an optional target length; the default is the target's limit (Telegram 4096, YouTube 5000) or a chosen size.
  The model is told to return the text only.
- Editor panel (reuse the shared pieces in `docs/UI.md`): buttons Rewrite and Shorten on the tab, the suggestion shown
  with a word diff against the current text (`TextDiff`), buttons Apply and Dismiss. Apply writes through the normal save
  path, so a version is recorded in History and the text is never lost.
- Settings: choose provider and model (already stored). If no key is set, the buttons explain it and link to Settings.

## Rules
- AI is **always a suggestion**. Never apply automatically, never publish automatically, never edit a published copy.
- The creator's own key is used. Keys stay encrypted, are never logged, never sent to the browser, never exported.
- The post text goes to the chosen provider: say so once in the panel and in the privacy page.
- Result over the target's limit is shown with the count and refused on Apply, like manual edits.
- Per-creator rate limit and a daily cap (setting) so a loop cannot burn the creator's key.
- Failures (bad key, quota, timeout) are shown in plain words and logged without the text or key.

## Done when
- With a valid key, Rewrite and Shorten return a suggestion on a Hebrew post and on an English post; Apply creates a
  version in History; Dismiss keeps the text.
- Without a key, or with the provider off, the buttons explain why and nothing is sent.
- A shortened text fits the target limit, or is refused with the count.
- Tests with a fake provider (no real keys, no real text in tests).

## Not in this step
- Collections, translation, hashtag ideas, scheduling suggestions, automatic AI on import (later, if asked).
- In-browser or local models. See the note below.

## Note: WebGPU / on-device models (owner question, 2026-11-19)
- Running a small model in the browser with WebGPU is possible (Transformers.js, WebLLM). It would let rewrite or
  shorten run with no key and no text leaving the device.
- Trade-offs: a large first download (hundreds of MB to GBs, cached afterwards), works only on browsers and phones with
  WebGPU and enough memory, quality is below hosted models, and Hebrew quality is weak in small models. Needs a vendored
  script (no Node build on Plesk) and care with the CSP.
- Speech to text (transcripts) can run the same way with a Whisper model in the browser. Text to speech is possible too.
  Not part of this step; if wanted, make it a separate step ("audio and transcripts"). Verify any named model (for
  example "BlueTTS") exists, its license, and its language support before planning around it.
- Suggested path: build this step with hosted providers first; add an "On this device" provider later behind the same
  `AiClient` interface, as an opt-in.

## Notes
- Related: `docs/steps/07-editor.md`, `docs/steps/10-settings-and-ai-keys.md`, `docs/DATABASE.md` (AI tables), `docs/UI.md`.
