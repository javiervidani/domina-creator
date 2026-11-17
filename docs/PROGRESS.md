# Progress (high level)

Update this file at the end of every step. Mark a step Done only when it runs on Plesk or in tests.

## Status
| # | Step | State |
|---|---|---|
| 0 | Planning, mockups, decisions | Done |
| 1 | Project setup and deploy to Plesk | Not started |
| 2 | Database, roles, creator schemas | Not started |
| 3 | Sign in and registration | Not started |
| 4 | Accounts and connections | Not started |
| 5 | Import and the one ID | Not started |
| 6 | Library page | Not started |
| 7 | Content editor | Not started |
| 8 | Publishing and re-publish | Not started |
| 9 | Dashboard and onboarding | Not started |
| 10 | Settings and AI keys | Not started |
| 11 | Collections and website embed | Not started |
| 12 | Deletion and 60-day recovery | Not started |
| 13 | CSV export and backups (last) | Not started |

## Done so far
- Mockups of the Library and the Content editor, including the AI suggestions panel.
- Decisions: Laravel on Plesk, PostgreSQL, schema per creator, no Redis, database queue, review-first publishing,
  manual re-publish, 60-day recovery.
- Repo `domina-creator` created and documented.
- Deploy flow tested: push to `main` reaches the Plesk domain through the webhook.

## Open questions
- PHP on the Plesk domain is 8.3.36 with pdo_pgsql, mbstring, openssl, zip and the other Laravel extensions: Laravel 12 or newer fits.
- Plesk webhook works. The deploy folder is the web root today; Laravel needs the document root set to `public`.
- Sign-in library: Fortify with our own pages, or the current official starter kit.
- Tenancy: a package or our own small class.
- Repo name was confirmed as `domina-creator`. Domain name is not decided.

## Next
Step 1: create the Laravel project, wire the deploy, confirm it opens on Plesk. See `docs/steps/01-project-setup.md`.

## Blockers
- Claude's workspace cannot reach `repo.packagist.org` (egress policy), so Composer cannot install Laravel there.
  The owner must allow `repo.packagist.org` and `packagist.org` in the Claude settings (Capabilities, allowed domains).
