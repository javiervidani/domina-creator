# Step 2: Database, roles, creator schemas

**Goal:** a creator can be provisioned with a private schema, and the app can enter it safely.

## Do
- Central migrations: users, creators, creator_members, tenants, deletion_requests, jobs, failed_jobs, cache, sessions.
- A provisioning class that creates role `creator_<id>`, the schema, grants, and runs the creator migrations.
- A tenancy class that does `SET ROLE` and `SET search_path` for a request or a job, and resets it afterwards.
- A command that runs new creator migrations across all schemas.
- Central models set `$table = 'public.<name>'`.

## Done when
- Two test creators exist and neither can read the other's tables (tested).
- A request without a switch gets "permission denied".
- Queue jobs carry the creator id and switch before they run.

## Notes
- See `docs/DATABASE.md` for the role rules. Check `CREATEROLE` behaviour on the installed PostgreSQL version.
