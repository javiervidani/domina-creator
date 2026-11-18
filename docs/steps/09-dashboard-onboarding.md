# Step 9: Dashboard and onboarding

**Goal:** a new creator gets to a working setup quickly, and a returning creator sees what needs attention.

## Do
- Onboarding: connect an account, choose the source of truth, choose targets and Auto or Review first.
- Dashboard: needs-attention list (drafts, failed publishes, expiring tokens, source-changed items), recent imports,
  and the longer sync line ("Instagram to YouTube (auto), Telegram (review)").

## Done when
- A new account reaches its first imported post through the wizard.

## Built
- `app/Dashboard` (Attention, Onboarding, SyncLine), `DashboardController`, views in `resources/views/dashboard`.
- The wizard derives each step from the data (an Instagram account, a source of truth, a target or "skip for now" kept in the session, at least one content). Registration, login and the Instagram sign-in return into it while it is unfinished.
- The needs-attention list shows up to 5 of each kind, linking to the account page or the content.
- Not built: Collections and Settings tabs (steps 10 and 11), "failed" for imports (only publishes can fail on a copy; import failures show in the Accounts activity list).
- Tests: `SyncLineTest` (any DB), `DashboardTest` (PostgreSQL only).
