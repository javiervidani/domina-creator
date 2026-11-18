# Step 6 missing: Library page

## Done
- List with search, account, status and source-changed filters, Needs review view, pagination, bulk Combine and Tag, sync chip, drafts badge, read-only content page.

## Proven locally (2026-11-18)
- Real HTTP requests against a local PostgreSQL 16: list, pagination, Hebrew, every filter, Combine, Tag, drafts badge. See `PROGRESS.md`.

## Proven live (2026-11-18)
- Owner confirmed the Library page works on the live site. Combine and Tag live were not confirmed separately.

## Missing
- [ ] **Owner:** try the editable content page live: open a row, change the text, Save; and Combine two rows (parts show on the page).
- [ ] **Later (step 7):** the full editor (per-target tabs, history, linked-copy actions, AI panel). The current page only edits title and text.
- [ ] **Owner:** try Combine and Tag on the live Library and say so.
- [ ] **Claude:** tests have never run (see the step 2 file).
- [ ] **Later (step 7):** the Content editor. Combine lands on a read-only page until then.
- [ ] **Later (step 8):** the **Failed** saved view and a real **Needs review** (drafts waiting for approval). For now Needs review means source changed.
- [x] Add to collection (built in step 11).
- [ ] **Later (steps 9 and 10):** Dashboard, Collections and Settings tabs from the mockup. Only Library and Accounts exist, so no dead links.
- [ ] **Claude (small):** filter by tag. Tags can be added now but not filtered on; ask the owner whether it is wanted before step 7.
- [ ] **Claude (small):** restyle the Accounts and sign-in pages to the new look (Accounts already uses the new top bar).
