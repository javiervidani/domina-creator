# Design and front end

Only decisions from the planning chat are listed as decided. Anything else is under Open.

## Look (decided)
- Light and clean, Google-like. Style comes from Talkdeck Studio (`talkdeck/ui`, editor page first) and the approved mockups.
- Blue pill buttons. Fonts: Heebo and Rubik.
- The creator is the only user, so there is one UI: the dashboard. Audiences never sign in.

## Front end stack (decided)
- Bootstrap 5, vendored into `public/vendor/bootstrap` (CSS and JS, no CDN). No Node build on Plesk.
- Our own stylesheet `public/css/app.css` sits on top of Bootstrap and holds all colors, fonts and the pill button.
  Pages must not use inline styles or one-off colors.
- Livewire is the proposed way to build pages (see `STACK.md`, still Proposed).
- Why not Tailwind: it needs a build step, and Plesk has no Node build.

## Screens and layout rules (decided in the mockups)
- Phase 1 screens: Library (list of content) and Content editor. Others follow `docs/steps/`.
- Editor: text only in phase 1. Sources that make up a piece are shown in order. The AI suggestions panel sits in the
  editor but is phase 2 and uses the creator's own API key (Settings).
- Sync details (long status text) do not sit on the editor. They live in Settings, with only a short status in the UI.
- Per piece of content: one platform ID is shown, with its linked copies. A `source_changed` flag offers
  Keep mine, Take the new version, or Compare. Re-publish is a button, never automatic.

## Open
- Exact color values and spacing: take them from Talkdeck Studio when step 6 starts, then write them here.
- Hebrew and right-to-left support: not decided.
- Where the approved mockups are stored (they were made in chat and not saved in the repo). Add them to
  `docs/mockups/` before step 6.
