# Step 11 missing: collections and website embed

## Done
- Collections, items, visibility, embed settings, embed code, RSS and JSON, allowed sites, new link, Add to collection from the Library.

## Missing
- [ ] **Owner:** try it live. Create a collection from the Library (select posts, Add to collection), set it to *Anyone with the link*, add your website under allowed sites, copy the embed code, and paste it into a test page on **another domain**. That page is the done-when item; I cannot test it from here.
- [ ] **Owner:** open the RSS and JSON links once; check the JSON loads from your site (browser console shows no CORS error).
- [ ] **Claude:** tests have never run (see the step 2 file).
- [ ] **Claude:** drag-and-drop reorder (up and down buttons for now), a live preview inside the dashboard, light/dark look, and a cover picture shown in the embed are not built.
- [ ] **Claude (ask first):** the *Public* visibility only adds the readable address; there is no listing page of a creator's public collections.
- [ ] **Later (step 14):** pictures in the embed use the network's address and will break when it expires. Light copies (step 14) would fix it.
- [ ] **Later (step 13):** the export includes collections and items but never `feed_tokens`.
