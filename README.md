# Interior & Architecture Management ERP

A production-oriented ERP for interior design and architecture firms — lead to handover to warranty — built on Laravel 12 + Livewire 3.

See **`ARCHITECTURE.md`** for the full system design: ERD, table list, folder architecture, module dependency order, and the phase-by-phase roadmap. This README only covers getting the application running.

**Status:** Phase 1 (Foundation) — authentication, roles & permissions, settings, base layout, numbering, document storage, audit log. Every other module in `ARCHITECTURE.md` ships in its own later phase.

---

## 1. Requirements

- PHP 8.2+ with extensions: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd` (for image/logo handling)
- Composer 2.6+
- MySQL 8+ (or MariaDB 10.6+)
- Node 20+ and npm (for the Tailwind/Vite front-end build)
- A local mail catcher (Mailpit/Mailhog) is convenient in development but not required — `MAIL_MAILER=log` works out of the box

## 2. Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
```

## 3. Environment Configuration

Open `.env` and set at minimum:

| Variable | Purpose |
|---|---|
| `APP_URL` | Base URL the app is served from |
| `APP_TIMEZONE` | Defaults to `Asia/Dhaka` (spec §73) |
| `DB_*` | MySQL connection |
| `SUPER_ADMIN_PASSWORD` | Password for the seeded super admin — **set this before seeding in any shared environment**; defaults to `password` locally only |
| `UPLOAD_MAX_SIZE_KB` / `UPLOAD_ALLOWED_MIMES` | Enforced by `FileService` on every upload (spec §51) |
| `AWS_*` | Leave blank while `FILESYSTEM_DISK=local`; fill in to move uploads to S3-compatible storage later with no code change (spec §72) |

## 4. Database Setup

Create the database named in `DB_DATABASE`, then:

```bash
php artisan migrate
```

## 5. Seeding

```bash
php artisan db:seed
```

This runs, in order: `CompanySeeder` (one company row — the app is multi-company-ready but single-tenant in v1), `SettingsSeeder` (default company/finance/prefix/mail settings), `RolePermissionSeeder` (generates the full granular permission set and the 11 staff roles — see its doc-block for why Client/Vendor/Subcontractor are excluded), `SuperAdminSeeder` (logs a one-time super admin credential to the console — **change this password immediately outside local development**).

Re-running `db:seed` is safe: every seeder uses `firstOrCreate`/`updateOrCreate`/`syncPermissions`, so it will not duplicate data or wipe permission customizations an Admin made through the UI, beyond re-syncing the *default* role→permission mapping.

`DemoSeeder` (spec §65 — demo leads/clients/projects/products/vendors/quotations/invoices/expenses) does not exist yet; it is added once the modules it needs exist, starting Phase 2.

## 6. Storage Linking

```bash
php artisan storage:link
```

Required for the public `uploads`/`public` disks (logos, exported files) to be reachable over HTTP. Document uploads go through `App\Services\FileService`, never a raw `Storage::put()` call, so MIME/size validation is never bypassed.

## 7. Queue Setup

Background jobs (PDF generation, exports, notification dispatch — added as their modules ship) use the `database` queue driver by default. In development:

```bash
php artisan queue:listen
```

In production, run a persistent queue worker under a process manager (Supervisor, systemd):

```bash
php artisan queue:work --tries=3 --backoff=30
```

## 8. Scheduler

`routes/console.php` documents every scheduled command as each module adds one (overdue invoices, low-stock alerts, follow-up reminders, warranty expiry — spec §67). Nothing is scheduled yet in Phase 1. Local development:

```bash
php artisan schedule:work
```

Production — a single crontab entry:

```
* * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1
```

## 9. Local Development

```bash
composer run dev
```

Runs the dev server, queue listener, log tailer (`pail`), and Vite dev server together. Or individually:

```bash
php artisan serve
npm run dev
```

Visit `http://localhost:8000/login` and sign in with the super admin credential printed by the seeder.

Three separate login surfaces exist from Phase 1 (guards only — portal screens are Phase 11):

- `/login` — staff (`web` guard)
- `/client/login` — client portal (`client` guard)
- `/vendor/login` — vendor portal (`vendor` guard)

## 10. Running Tests

```bash
composer test
# or
php artisan test
```

Tests run against an in-memory SQLite database (`phpunit.xml`) so no separate test database setup is needed. Covers: guard separation (a client's correct password never authenticates on the staff guard, and vice versa), permission gating and the Super Admin bypass, the 11-role seed, atomic document numbering, Settings CRUD, and activity-log write/no-delete behavior.

> Note on numbering concurrency: SQLite's `lockForUpdate()` is a no-op on a single connection, so true concurrent-process safety (spec §54) is only conclusively proven against MySQL — run the suite with `DB_CONNECTION=mysql` in a staging environment, or exercise `NumberingService::next()` from two parallel `artisan tinker` processes, to confirm no collision under real contention.

## 11. Production Deployment

1. `composer install --no-dev --optimize-autoloader`
2. `npm ci && npm run build`
3. Set `APP_ENV=production`, `APP_DEBUG=false`, a real `APP_KEY`, and strong DB credentials
4. `php artisan migrate --force`
5. `php artisan db:seed --force` only on first deploy (or skip and create the super admin manually)
6. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
7. Point the web server's document root at `public/`
8. Run a queue worker under Supervisor/systemd (see §7)
9. Add the scheduler crontab entry (see §8)
10. Serve over HTTPS — `bootstrap/app.php` forces the HTTPS URL scheme in production

## 12. Backup

Not automated in Phase 1 (spec §68 explicitly rules out building dangerous automatic deletion). Recommended strategy for later phases to formalize:

- **Database:** nightly `mysqldump` (or your managed MySQL provider's automated snapshot), retained on a rolling window, stored off-server
- **Files:** the `storage/app/public/uploads` tree (or the S3 bucket, once cloud storage is enabled) backed up on the same schedule as the database so a restore keeps documents and their database references in sync
- Test the restore procedure periodically — a backup that has never been restored is unverified

## 13. Troubleshooting

| Symptom | Likely cause |
|---|---|
| `419 Page Expired` on every form submit | Session cookie/domain mismatch — check `SESSION_DOMAIN` matches `APP_URL`'s host |
| Login succeeds but immediately redirects back to login | Guard mismatch — confirm you're hitting the matching login route for the account's `user_type` (`/login`, `/client/login`, `/vendor/login`) |
| `403` on every page after login | The user has no role/permission assigned yet — run `php artisan tinker` and `$user->assignRole('...')`, or grant `manage_settings` to reach the Settings UI and assign roles there once a Users screen exists (Phase 9) |
| Uploaded file rejected | Check `UPLOAD_MAX_SIZE_KB` / `UPLOAD_ALLOWED_MIMES` in `.env` against the file's actual size/extension — `FileService` enforces both server-side regardless of any client-side limit |
| Logo/upload 404s after upload | Run `php artisan storage:link` |
| Numbers look reused after a crash mid-request | Shouldn't happen — `NumberingService::next()` only commits the increment inside a committed transaction; if seen, check for a direct, unprotected write to `document_sequences` bypassing the service |

---

## Project Structure

See `ARCHITECTURE.md` §5 for the full folder layout and the reasoning behind it (Services layer, Policies, Livewire component tree, three separate route files for the three portals, etc.).
