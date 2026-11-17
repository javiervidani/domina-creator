# Stack and libraries

Status: **Decided** (agreed in chat), **Proposed** (recommended, not confirmed), **Later** (not in the first build).

## Hosting
| Item | Choice | Status |
|---|---|---|
| Server | Plesk on IONOS | Decided |
| Database | PostgreSQL, one new database | Decided |
| Redis | Not available | Decided |
| Queue | Laravel `database` driver, run by cron (`queue:work --stop-when-empty` each minute) | Decided |
| Scheduler | One Plesk cron entry running `schedule:run` each minute | Decided |
| Cache and sessions | `database` or `file` | Decided |
| Deploy | GitHub webhook to Plesk Git extension, then `composer install --no-dev` and migrations | Decided |
| PHP version | Not known yet. It decides the Laravel version | Open |

## Application
| Library | Use | Status |
|---|---|---|
| Laravel | Framework | Decided |
| Livewire | Library and Editor pages | Proposed |
| Bootstrap 5 (vendored, no build) plus our own stylesheet | Look of the mockups, no Node build on Plesk | Proposed |
| Laravel Fortify with our own pages, or the current official starter kit | Sign in, registration, password reset | Open |
| Laravel Socialite and SocialiteProviders | Connecting creators' accounts through OAuth | Decided |
| Laravel Sanctum | API tokens for embed and feed endpoints | Proposed |
| Laravel HTTP client | Instagram Graph API calls (reuse the logic of `cnx-instagram-sync`) | Decided |
| laravel-notification-channels/telegram | Publishing to Telegram | Proposed |
| google/apiclient or hand-written calls | YouTube uploads | Open |

## Content and data
| Library | Use | Status |
|---|---|---|
| spatie/laravel-tags | Tags on content | Proposed |
| spatie/laravel-activitylog | History tab in the editor | Proposed |
| jfcherng/php-diff | Compare view when the source changed | Proposed |
| spatie/laravel-permission | Roles inside a creator's team | Later |
| spatie/laravel-medialibrary | Media files | Later |
| spatie/laravel-feed | RSS feeds for creators' sites | Later |
| A multi-tenancy package (stancl/tenancy or spatie/laravel-multitenancy) | Schema switching. Check that it supports schema per tenant on PostgreSQL, or write our own small class | Open |

## Quality and operations
| Library | Use | Status |
|---|---|---|
| PHPUnit | Tests (skeleton default, instead of Pest) | Chosen |
| Larastan and Laravel Pint | Static checks and code style | Proposed |
| Laravel Pulse | Queue and slow-job monitoring (works without Redis) | Proposed |
| Laravel Horizon | Needs Redis, so not used | Decided |
| Filament | Only if we want ready-made admin tables later | Later |

## Out of scope for now
- Laravel Passport (we are not an OAuth server).
- AI provider libraries. In phase 2 the creator supplies their own key (Claude, OpenAI, Grok, Groq, others).
- `pg_dump` backups. They are the last step. See `docs/steps/13-export-and-backup.md`.

Always check a package's current version and Laravel compatibility before installing it.
