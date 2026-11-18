# domina-creator: the platform

A private workspace for creators and mentors. The creator connects the social accounts they already use, the
platform collects that content in one place, the creator edits it there, and the platform sends it back out.

## Who it is for
- Creators and mentors. They are the only users. Their audience never logs in and sees the content on social
  networks or on the creator's own website.

## What it does
1. **Collect.** Pull posts and videos from connected accounts (Instagram first) on a schedule.
2. **One ID.** Every piece of content gets one platform ID. Copies on other networks link back to it.
3. **Edit.** Work on the text in one editor: combine sources, write a different version per target, see what changed
   at the source.
4. **Send out.** Publish or re-publish to the other accounts, automatically or after review.
5. **Show on the creator's website.** Collections, embed code, and RSS/JSON feeds (later phase).

## Rules of the model
- One account may be the **source of truth** for outward sync. Any account may be an **import** source.
- Editing happens on the platform copy. A change at the source never overwrites an edit. The creator chooses.
- A published copy is never changed automatically. The creator presses **Re-publish**.
- New targets start as **review first**. Auto-publish is a switch the creator turns on.
- Phase 1 is text and manual editing, with the post's image or video shown and sent by link only (never copied). AI suggestions come in phase 2 with the creator's own API key.

## Privacy model
- Each creator has a separate PostgreSQL schema (`creator_<id>`) in one database.
- Deleting a creator's history locks the schema, waits 60 days, then drops it. See `docs/DATABASE.md`.

## Screens
Sign in, Onboarding, Dashboard, Accounts, Library, Content editor, Collections, Settings.
Sync status, activity log, billing, and the website embed live inside those pages, not on pages of their own.

## Where to read next
- `docs/STACK.md`: libraries and hosting.
- `docs/DATABASE.md`: schemas, roles, tables, deletion and recovery.
- `docs/PROGRESS.md`: where the build stands.
- `docs/steps/`: one file per build step.
- `CLAUDE.md`: the rules every code change follows.
