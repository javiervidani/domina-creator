# Step 18 (idea): a book from the creator's content

**Status: concept only, written 2026-11-25 (owner idea). Nothing decided, nothing built.** Speech to text is in the backlog meanwhile.

## The idea
Turn what the creator already wrote and said (posts, YouTube transcripts, collections) into a book they can read, edit and export. The creator stays the author: AI only suggests, nothing is applied without a click (same rules as steps 15 and 17).

## Concept
- **Raw material:** post texts, transcripts of lectures, collections and tags (themes), posts marked "my voice" (the style).
- **Posts are not chapters.** Posts are short, repeat themselves and depend on their day. A book needs an order, one voice, no duplicates, transitions, an opening and an ending.
- **Three kinds of book** (pick one first):
  1. *Compilation*: each post is a page or a short piece, grouped by theme, almost no rewriting (a book of reflections).
  2. *Thematic*: chapters by theme, each chapter merges several posts into flowing text in the creator's style.
  3. *Course or lecture book*: a transcript becomes a chapter, restructured and cleaned (needs the most AI and the best transcripts).
- **Flow:** Collect, Organise (chapters), Outline, Draft each chapter, Edit, Front and back matter, Export.
- **Size check:** a post is about 120 Hebrew words. 25 posts is about 3,000 words, a booklet. A 100 page book is roughly 25,000 words, about 200 posts, or several lecture transcripts (a 30 minute lecture is about 4,000 words). Transcripts are what makes a real book possible.

## Practical design (reuse what exists)
- **Chapter = a Content.** The editor, History, Combine (sources in order), AI Rewrite and Shorten, My style and transcripts all work on it unchanged.
- New tables in the creator schema: `books` (title, subtitle, author, language, direction, status) and `book_items` (book, position, kind: chapter | front | back | divider, content id or own text). Collections already give ordering and can seed a book.
- **Export:** start with what needs no heavy software: a clean print view (browser Save as PDF, with right-to-left support), EPUB (a zip of XHTML we can write ourselves, `dir=rtl`, `lang=he`) and DOCX (publishers and self-publishing sites accept it; check the library's Hebrew right-to-left support before choosing). PDF libraries differ a lot in Hebrew; test before choosing.
- **Pictures:** text first. Kept pictures are 640 px, too small for print. A cover is an uploaded image for now.

## Phases
1. **Manual builder (no AI):** create a book, add chapters from the Library or a collection, reorder, front and back matter, export print view, EPUB and DOCX.
2. **AI helpers (suggestions only):** propose an outline by theme from the selected posts; merge several posts into one chapter draft in My style; write a transition, an introduction or a closing; flag duplicates.
3. **Polish:** table of contents, page numbers, templates, cover, notes on ISBN and self-publishing.

## Risks and rules
- AI can change meaning or invent: keep the post's facts, show a comparison, History first (already how step 15 works).
- Hebrew typography: punctuation at the start or end of right-to-left lines, quotes, numbers and nikud need testing in every export format.
- Rights: the creator's own words only; quotes of other people need their permission. The platform does not check this.
- Cost: AI per chapter is small at today's prices; the daily cap from step 15 applies.
- A book is creator data: it goes with the history at day 60 and is in the CSV export.

## Open questions for the owner
1. Which kind of book first: compilation, thematic or course?
2. For print, an ebook, or a PDF to share?
3. How much should AI write, and how much should stay the creator's own words?
4. Is a first booklet from the 24 marked posts a good test, or wait for transcripts?
