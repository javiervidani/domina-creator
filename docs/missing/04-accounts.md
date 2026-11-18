# Step 4 missing: accounts and connections

## Done
- Accounts page, encrypted tokens, own Socialite provider for Instagram Login, `RefreshTokenJob`,
  `accounts:refresh-tokens`, `SyncAccountJob` (reads one page, records the run), targets UI (review first by default).

## Missing
- [ ] **Owner, waiting on Meta:** the Instagram connection has never been tried. Needed:
  1. Meta app with the Instagram Login product. In `.env`: `INSTAGRAM_CLIENT_ID`, `INSTAGRAM_CLIENT_SECRET`,
     `INSTAGRAM_REDIRECT_URI=https://domina-creator.datashellv.com/accounts/instagram/callback`; then `config:cache`.
  2. The redirect URI added in the Meta app. These keys belong to the platform, set once, not per creator.
  3. Development mode: only Instagram testers can connect. Other creators need the app Live, with Meta's approval of
     `instagram_business_basic`.
  4. The creator's Instagram must be a Professional account.
- [ ] **Claude (needed before Live):** Meta requires a **deauthorize callback** and a **data deletion request URL**. Add the
  two endpoints (they remove the account and its token), and a privacy URL (see step 3 file).
- [ ] **Claude:** test the whole flow with a fake Instagram (fake HTTP for the token, exchange, `me` and media calls), so
  it can be checked without waiting for Meta.
- [ ] **Claude:** token expiry notice on the page is only a badge. Add a banner when any account is "expiring" or "reconnect".
- [ ] **Claude:** the callback error handling only shows one generic message. Show Instagram's reason when it gives one.
- [ ] **Owner:** cron for `schedule:run` (weekly token refresh) and a running queue (Sync now and refresh are queued jobs).
- [ ] **Claude:** server-side guard for source of truth. Today only the confirm dialog protects it.
- [ ] **Later (step 5):** dispatch syncs by `import_frequency` and store the content. Stored frequency is unused until then.
- [ ] **Later:** connectors for YouTube and Telegram targets. Targets can already be created between accounts, but only
  Instagram exists as a platform.
