# UI references

Approved mockups (Claude artifacts, owner's account). Use them for the matching step; do not build their screens earlier.
- Library and a simple editor: https://claude.ai/artifact/9kNqPFpF7UsFzDRuLDqjPX (steps 6 and 7)
- Content editor (3 columns, per-target tabs, AI panel, source-changed banner): https://claude.ai/artifact/EfNBPC8yEaMxq4UmaFEXao (step 7; the AI panel is phase 2)

## Style (from both mockups)
- Top bar: brand "Creator **Studio**", tabs Dashboard, Library (badge = drafts to review), Collections, Accounts, Settings, and a "Synced N min ago" pill with a status popup on the right.
- Colors: ground `#f3f6fc`, line `#dde3ea`, ink `#1f1f1f`, blue `#0b57d0` (hover `#0a4bb5`, soft `#d3e3fd`, tint `#eaf1fe`), good `#146c2e`/`#d7f5df`, warn `#8a5300`/`#ffe9b8`, bad `#b3261e`/`#fce8e6`.
- Fonts: Heebo (text), Rubik (headings). Cards radius 16px, pill buttons, status pills.
- Platform badges: IG `#c13584`, YT `#d93025`, TG `#229ed9`.
- Steps 3 and 4 pages use plain Bootstrap for now. Restyle them to this top bar when step 6 builds the shared layout.
- Content text is often Hebrew (right to left). Text areas, titles and previews need `dir="auto"` so each piece lays out in its own direction; check this in the Library and editor (steps 6 and 7).

## Shared building blocks (reuse these; do not add a class for one page)
One stylesheet, `public/css/app.css`. Before writing new CSS, use what is there:
- **Layout:** `.page` (content width and padding), `.card` (Bootstrap card, our radius), `.studio-nav` (top bar, tabs strip, account menu), Bootstrap `.nav-tabs` for tab rows (they scroll sideways on a phone and the open tab scrolls into view).
- **Labels:** `.pill` (+ `blue`, `good`, `warn`, `bad`) for status; `.badgex` for counts; `.cid` for the `CNT-n` id (always one line, left-to-right); `.pf` + `instagram|youtube|telegram` for platform icons; `.chip` / `.chips` for filter pills.
- **Rows and buttons:** `.lib-row` + `.lib-meta` for a list row (text, platforms, status); `a.row-link` for a plain row link; `.icon-btn` for move up, move down, remove (finger-sized); Bootstrap `btn btn-sm rounded-pill` for every button.
- **Forms:** Bootstrap `form-label small` + `form-control` / `form-select`; the shared partials `partials/_target_form.blade.php` ("add a target", used by Accounts and the wizard).
- **Text:** put `dir="auto"` on anything that shows the creator's words (titles, texts, names). Keep our own English label and the creator's title on separate lines, so Hebrew never garbles a sentence.
- **Phone first:** check a new page at 390 px wide with no sideways scroll before calling it done. Render it with sample data in Chromium (Playwright is installed) instead of guessing.
