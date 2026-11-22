# Step 17 (phase 2): My style, the creator's own voice

**Status: planned, not started. Do not build until the owner says so.** Written 2026-11-22 (owner request). Follows step 15
(AI Rewrite and Shorten, tested live with OpenAI on a Hebrew post).

**Goal:** the AI's output sounds like the creator, not like "AI style". The creator can give their own instruction and can
teach the platform their voice from their own posts. Output only: text suggestions. No agent, nothing acts by itself.

## Part 1: own instruction (small)
- A text box next to Rewrite and Shorten: one-off instruction ("warmer, short sentences, no exclamation marks").
- Saved instructions per creator (a name and the text), picked from a list, one can be the default.
- The post text still goes as data; the instruction goes in the system prompt after our fixed rules (keep meaning, facts,
  language and direction, links, hashtags). The creator's instruction can change style, never the fixed safety rules.

## Part 2: My style (the main feature)
- The creator marks 10 to 30 of their own posts as "my voice" (a tag or a switch on the post), and can unmark them.
- From these we keep two things per creator:
  - a **style guide**: a short description of their voice (sentence length, tone, vocabulary, how they open and close,
    emoji and hashtag habits, language mix). The AI drafts it from the marked posts; the creator reads, edits and owns it.
  - **example posts**: a few real posts added to each request so the output imitates their sentences. Start with 3 to 5
    chosen at random or the newest marked ones.
- Rewrite and Shorten get a switch "In my style" (on once a guide exists). The suggestion panel says which guide and
  examples were used.
- No model training and no fine-tuning: it is prompting with the creator's own material, so it works with the key they already have.

## Part 3: retrieval (RAG), later and only if the library is large
- Choose the examples by topic: the marked posts most similar to the post being edited.
- The owner says this PostgreSQL has the `vector` type (pgvector). If so: one embedding per marked post (an embeddings call
  to the creator's own provider, for example OpenAI), stored in a `vector` column in the creator schema, nearest-neighbour search.
- **Check before building:** the creator role has `public` out of its search path, so the vector operators must be written
  qualified (for example `OPERATOR(public.<=>)`) or the extension installed where the creator role can use it. Confirm
  which schema the extension lives in and that the creator role may use it. A library of a few hundred posts can also be searched
  in plain PHP over stored arrays if pgvector turns out awkward.
- Embeddings are creator data: removed with the history at day 60.

## Rules
- Always a suggestion with Apply or Dismiss (step 15). The creator can switch "In my style" off.
- The creator's posts and the style guide are sent to the chosen AI provider when used: say so in the panel and on the privacy page.
- Style guide, examples and embeddings belong to the creator's schema and go with the history at day 60.
- Never present a text as the creator's own writing without their Apply.

## Done when
- A one-off instruction changes the suggestion as asked, and a saved instruction can be reused.
- With 10 or more marked posts, a style guide is drafted, editable and saved; "In my style" suggestions use it and the examples.
- Switching it off returns to the plain behaviour. Tests with a fake provider (no real keys or posts).

## Notes
- Related: `docs/steps/15-ai-suggestions.md`, `docs/steps/10-settings-and-ai-keys.md`, `docs/DATABASE.md`.
