# Step 7: Content editor

**Goal:** edit one piece, or a combined piece, and see its linked copies.

## Do
- Sources list (parts): reorder, remove, Add source (Library posts, pasted text, video transcript).
- Tabs: Master text, YouTube, Telegram. A target tab can be "same as master" or its own text, with its limit shown.
- Source-changed banner: Keep mine, Take the new version, Compare (php-diff).
- Right panel: Linked copies under the one ID, and History. The AI suggestions tab is added in phase 2.
- Autosave. Originals are never edited.

## Done when
- Edits save, versions appear in History, and Compare shows the differences.

## Built
- `app/Content` (Targets, VersionRecorder, TextDiff), controllers `ContentController`, `ContentTargetController`, `ContentSourceController`, `ContentPartsController`, views in `resources/views/content*`.
- Autosave (700 ms after typing stops, JSON). Edits within five minutes merge into one History entry; Keep mine, Took the new version and Rebuilt from sources get their own entries. The old text is saved in History before taking the new version.
- Adding a source appends its text to the master text; move and remove only change the list; Rebuild replaces the master text from the list.
- Video transcript is a pasted text for now (fetching a transcript needs the YouTube connector).
- Not built: restore from History, undo/redo and formatting toolbar (plain text only in phase 1), the AI suggestions tab (phase 2).
