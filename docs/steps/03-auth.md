# Step 3: Sign in and registration

**Goal:** a creator can register, sign in, reset a password, and gets a workspace (creator schema).

## Do
- Decide the library: Fortify with our own pages, or the current official starter kit.
- Registration creates the user, the creator record, and triggers provisioning (step 2).
- Pages use the mockup style: Bootstrap 5 vendored plus our own stylesheet.
- Roles inside a creator's team: owner first, others later.

## Done when
- Register, sign in, sign out, and reset all work on Plesk.
- A new registration ends on the empty Library page of its own schema.

## Built
- Plain Laravel auth, no package: `app/Http/Controllers/Auth`, `app/Tenancy/EnterTenancy` (alias `tenant`), `LibraryController`, views in `resources/views`.
- Tests: `AuthPagesTest` (any DB), `RegistrationTest` (PostgreSQL only).
- Owner: set `MAIL_*` in `.env` for reset emails; run `migrate` is not needed (no new tables).
