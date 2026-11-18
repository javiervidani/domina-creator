# Step 11: Collections and website embed

**Goal:** group content and show it on the creator's own website.

## Do
- Collections: list, drag to reorder, cover and description, visibility (private, link, public).
- Website embed tab: choose a collection, layout and look, copy the embed code, RSS and JSON links.
- Allowed domains for the widget. Tokens for feed access (Sanctum or feed tokens).

## Done when
- A test page on another domain shows a collection through the embed code.

## Future, asked by the owner (2026-11-18): media in collections and the embed
- Collection items and the website embed can show the linked image or video thumbnail. Because addresses expire, the embed must not hot-link Instagram addresses from visitors' browsers for long-lived pages: decide whether the widget shows the thumbnail only while fresh, or links to the original post instead.

## Built
- `app/Collections` (AllowedDomains, TokenIssuer, FeedPayload, Rss), controllers `CollectionsController`, `CollectionItemsController`, `EmbedSettingsController`, `EmbedController`, public routes in `routes/public.php` (loaded without the web group, no closures so `route:cache` works).
- A link token is `<creator id>.<random>`: the id only says which schema to enter (and only an active creator is served); the random part is checked by hash. A new link revokes the old one at once. Visibility: private returns 404 everywhere; link needs the token; public also has `/c/<creator id>/<slug>`.
- The embed is an iframe of a server-rendered page. No allowed site means `frame-ancestors 'none'` (nobody can frame it). The embed code includes a small script that resizes the frame (it only trusts messages from this platform's own address).
- The text shown is the master text, escaped; the picture (if switched on) is the stored link with the network's address, hidden if it has expired, always with a link to the original post. Decision for the media note above: the widget does not try to keep expired addresses alive.
- Reorder uses up and down buttons, not drag and drop. Sanctum is not used.
- Tests: `AllowedDomainsTest` (any DB), `CollectionsTest` (PostgreSQL only).
