# Missing elements per step

One file per finished step: what is built, what is still missing, and who does it. Update a file when an item is done, and
move it to "Done" instead of deleting it. Items marked **Owner** need the owner (a server or Meta setting), **Claude** can
be built in the repo, **Later** belongs to a later step and must not be built early (see `CLAUDE.md`).

- `02-database-and-tenancy.md`
- `03-auth.md`
- `04-accounts.md`

## Owner checklist (all steps)
- [ ] Rotate the ops token (it was shared in chat). Set `APP_NAME=domina-creator` in the server `.env`.
- [ ] `MAIL_*` in `.env`, then send one password reset to prove mail works.
- [ ] Cron: `schedule:run` every minute, and the queue (Toolkit Queue tab, or cron `queue:work --stop-when-empty`).
- [ ] Meta app for Instagram Login (see `04-accounts.md`). Waiting on Meta, it takes time.
- [ ] Run the PostgreSQL tests on a machine with dev dependencies (the server has none: `--no-dev`).
