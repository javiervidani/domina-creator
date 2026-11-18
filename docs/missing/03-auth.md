# Step 3 missing: sign in and registration

## Done
- Register (creates user, creator, member, schema), sign in and out with throttling, password reset, `tenant` middleware,
  empty Library page. Registration and sign-in work on Plesk.

## Missing
- [ ] **Owner:** password reset needs working `MAIL_*`; not tested yet.
- [ ] **Claude:** tests have never run (see step 2 file).
- [ ] **Claude:** throttle the register form (login and reset are throttled).
- [ ] **Claude:** restyle the pages to the mockup top bar and tokens in `docs/UI.md` (done with the shared layout in step 6).
- [ ] **Claude:** a small profile page: change name, email, password. Not in the step list, so ask the owner first.
- [ ] **Later:** email verification, two-factor sign-in, and inviting team members (only the `owner` role exists).
- [ ] **Owner:** privacy policy and terms pages. Meta needs a privacy URL for the Instagram app, and `DATABASE.md` says the
  backup retention must be stated there.
