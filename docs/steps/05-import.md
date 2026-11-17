# Step 5: Import and the one ID

**Goal:** new posts from connected accounts land in the Library with one platform ID each.

## Do
- A scheduled pull per account, queued, with retries and a run record.
- Reuse the fetch logic of `cnx-instagram-sync` (token, media list, paging).
- Create `contents` and a source copy for each new post.
- Duplicate detection: link a match on another account to the existing ID.
- Flag `source_changed` when the original text changes after import. Never overwrite an edit.

## Done when
- A scheduled pull imports new posts once, with no duplicates on a second run.
- A changed original shows as `source_changed` and the edited text is untouched.
