# Step 4: Accounts and connections

**Goal:** the creator connects Instagram and sets how it syncs.

## Do
- Accounts page: list of connected accounts with health (OK, token expiring, reconnect).
- Connect through OAuth (Socialite). Store tokens encrypted. Refresh tokens on a schedule.
- Per account: Import on or off, frequency, Sync now.
- One Source of truth badge. Targets with Auto or Review first (default review).
- Activity log of sync runs on this page.

## Done when
- Instagram connects, a token refresh job runs, and errors show on the page.
- Changing the source of truth warns the creator and moves the badge.

## Built
- Creator tables: `connected_accounts`, `account_tokens` (encrypted), `account_targets`, `sync_runs`.
- `app/Platforms` (BaseConnector, Instagram connector and Socialite provider), `app/Accounts/AccountHealth`, `app/Jobs/RefreshTokenJob`, `SyncAccountJob`, `accounts:refresh-tokens`.
- Jobs are dispatched `afterResponse()` because the queue table is in `public` and a creator role cannot write to it.
- Tests: `AccountHealthTest`, `InstagramConnectorTest` (fake HTTP), `AccountsTest` (PostgreSQL only).
- Not done here: the real OAuth round trip needs a Meta app and cannot be tested from this workspace. Run `creators:migrate` once after deploy so existing creators get the new tables.
