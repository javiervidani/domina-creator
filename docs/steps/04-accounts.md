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
