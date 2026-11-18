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
