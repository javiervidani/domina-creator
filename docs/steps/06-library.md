# Step 6: Library page

**Goal:** the creator sees everything imported and can start editing or combining.

## Do
- List with search, filters (account, status, source changed), saved views (Needs review, Failed).
- Rows show the ID, text snippet, where the content lives (icons), and status.
- Select several rows, then Combine, Tag, Add to collection.
- Top bar shows a short sync chip ("Synced 12 min ago") with details in a dropdown, and a badge for drafts.

## Done when
- Filters and bulk actions work on real imported data.
- Combine opens the editor with the selected items as parts.

## Built
- `LibraryController` and `app/Library` (LibraryQuery, Combiner, NavStatus); list sorted by post date, newest first; `dir="auto"` for Hebrew.
- Bulk: **Combine** creates a new draft content whose parts are the selected items in list order, then opens `/content/{id}`. **Tag** adds one tag to the selected items.
- Top bar from the mockup: sync chip with a dropdown per account, and a drafts badge. Restyled `layouts/app` and `public/css/app.css`.
- Tests: `LibraryTest` (PostgreSQL only).
- The content page is a minimal editor (title and text, Save) so Combine opens something editable. Originals are never changed.
- Not built on purpose: **Add to collection** (button is disabled, collections are step 11), **Failed** saved view (needs publishing results, step 8), the full editor (step 7).
