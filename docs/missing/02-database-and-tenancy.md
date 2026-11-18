# Step 2 missing: database, roles, creator schemas

## Done
- Tenancy class, `public.provision_creator()`, creator migrations, `creators:migrate`, `CreatorJob`, central models.
- Live on Plesk: registration provisions a schema (after `database/setup/02-admin-grants.sql`).

## Missing
- [ ] **Owner:** confirm the schema really exists: `\dn` should list `creator_<id>` and `\du` the role.
- [ ] **Claude:** a proven test run. `TenancyTest` has never run (no dev dependencies on the server, no PostgreSQL in
  Claude's workspace). Needs a local run or a CI job with a PostgreSQL service. Done-when items are not proven until then.
- [ ] **Claude:** provisioning cleanup. A failed registration deletes the central rows, but a half-made role or schema can
  stay. Add a safe `creators:repair {id}` that finishes or removes it.
- [ ] **Claude:** `creators:migrate` stops at the first failing schema. Report every schema and continue.
- [ ] **Claude:** reference tables the creator role may read from `public` (plan limits). No such table exists yet, so
  add the grant together with the first one.
- [ ] **Claude:** document `02-admin-grants.sql` in `HANDOFF.md` as a required setup step (done in the docs update below).
- [ ] **Later (step 12):** deletion flow: revoke `app_user` membership, 60-day purge, restore. Only the tables exist.
- [ ] **Later (step 13):** per-creator `pg_dump` backups. Needs `pg_dump` on the server.
- [ ] **Owner:** `/ops/health` shows commit `unknown`. Write the deployed commit id to `storage/app/deploy-commit` in
  the deploy step (Plesk's deploy folder is not a git repo, so this needs an environment variable or a file from the hook).
