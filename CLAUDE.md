# domina-creator

A private platform for creators and mentors on Laravel and PostgreSQL, hosted on Plesk (IONOS).
Owner: Aviel. Read `PLATFORM.md` first, then `docs/STACK.md`, `docs/DATABASE.md`, `docs/PROGRESS.md`.
Work one step at a time from `docs/steps/`. Keep answers short and actionable.

## Prompt for Claude
You are the developer of domina-creator. The platform collects a creator's content from their social accounts,
lets them edit it in one place, and sends it back out. The creator is the only user. Build only the current step.
Do not add features from later steps. If a decision is open in `docs/PROGRESS.md`, ask the owner before choosing.
Never put secrets, tokens, or real creator data in the repo, in logs, or in tests.

## Code rules
- **File size:** every code file is 150 to 200 lines at most. When a file grows past that, split it.
- **Start with a main class, then extend it.** Build a small base class for each area (a base service, a base job, a
  base controller or Livewire component, a base platform connector). Add behaviour in subclasses only when needed.
  Do not add wrapper layers or abstractions that nothing uses yet.
- **Logical directories.** Every file lives in the folder that matches its job, for example:
  `app/Tenancy`, `app/Accounts`, `app/Import`, `app/Content`, `app/Publishing`, `app/Platforms/<Network>`,
  `app/Livewire/<Page>`, `app/Jobs`, `app/Models`, `database/migrations/central`, `database/migrations/creator`.
- Keep code simple and flat: few files per folder, clear names, no clever tricks.
- Comments explain why, not what.

## Database rules
- Follow `docs/DATABASE.md` exactly: schema `creator_<id>`, role `creator_<id>` (NOLOGIN), login role `app_user`
  (NOINHERIT), `SET ROLE` and `SET search_path` per request and per job.
- `public` is not in the search path. Always qualify central tables (`public.users`).
- No foreign keys from creator tables to `public` tables.
- Every queue job carries the creator id and switches schema before it touches data.
- Tokens and AI keys are encrypted at rest and never exported.

## Product rules
- One platform ID per piece of content. Copies on other networks link to it.
- Never overwrite an edited text because the source changed. Flag `source_changed` and let the creator choose.
- Never change a published copy automatically. Re-publish is a manual action.
- New targets default to review first.
- Phase 1 is text only. No AI until phase 2.

## Environment
- No Redis. Queue, cache, and sessions use the database or files. Horizon is not used.
- Plesk runs the scheduler and `queue:work --stop-when-empty` from cron.
- Bootstrap 5 is vendored (no Node build on Plesk). Our own stylesheet sits on top of it.
- Deploy is a GitHub webhook to the Plesk Git extension on `main`.
- Check a package's current version and Laravel compatibility before installing it.

## Working rules
- Until the owner says the baseline is reached, work directly on `main`: commit and push each finished piece. After
  the baseline, go back to committing only when the owner asks.
- Claude cannot read server logs on Plesk. Errors reach Claude only if the owner pastes them, or if the app records
  them where Claude can see them (see `docs/steps/01-project-setup.md`).
- After finishing a step: update `docs/PROGRESS.md`, update the step file, and add tests.
- Run Pint, Larastan, and Pest before saying a step is done.
- If something in these docs is wrong or missing, say so and fix the doc in the same change.

## Reference
- The sync logic to reuse is in the `community-nexus` repo: `wp-content/plugins/cnx-instagram-sync`.
- Look and feel follows the Talkdeck Studio style (light, Google-like, blue pill buttons) and the approved mockups.
