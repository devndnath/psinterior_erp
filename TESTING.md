# Testing & Validation Guide

All 12 phases of this build (Foundation through Reports/Portals) were written and
statically verified in a sandbox that could never run `composer install`,
`php artisan migrate`, or `php artisan test` — Packagist was blocked for the
entire build. Every phase was checked with `php -l`, manual reading against
Laravel/Livewire semantics, and the kind of audits Phase 12's hardening pass
describes in `claude/PHASE12_SUMMARY.md`. **None of it has ever executed
against a real database.** This document is the exact sequence to actually
run it for the first time, plus a manual smoke-test checklist that exercises
every phase end-to-end. It assumes you've already read `README.md` §1–§3 for
requirements and basic `.env` setup — this file goes one step further and
assumes you're validating the whole system, not just getting it to boot.

## 1. First-time setup (exact commands)

```bash
# 1. Dependencies
composer install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Edit .env — set at minimum:
#    DB_CONNECTION=mysql
#    DB_HOST=127.0.0.1
#    DB_PORT=3306
#    DB_DATABASE=psinterior_erp
#    DB_USERNAME=<your user>
#    DB_PASSWORD=<your password>
#    SUPER_ADMIN_PASSWORD=<set a real password — do not leave the default>

# 4. Create the database itself (MySQL 8+ / MariaDB 10.6+)
mysql -u root -p -e "CREATE DATABASE psinterior_erp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 5. Front-end assets
npm install
npm run build        # or `npm run dev` / `composer run dev` while actively testing

# 6. Migrate + seed in one step
php artisan migrate --seed

# 7. Storage symlink (logos, exported files, uploaded documents)
php artisan storage:link

# 8. Automated test suite
php artisan test
# or
composer test
```

`php artisan migrate --seed` is the step that actually proves or disproves
every migration this build shipped across 12 phases — including the index/
foreign-key/unique-constraint names Phase 12's audit checked by parsing the
migration files by hand (§2 of `claude/PHASE12_SUMMARY.md`). If any migration
fails here, it is the single most valuable bug report you can bring back to a
future session, because it is the one category of error static review
genuinely cannot see.

`php artisan test` then proves or disproves the ~40 Feature/Unit test files
under `tests/` (auth guard separation, permission gating, every Policy's
authorization boundary, numbering concurrency guarantees, and a handful of
full CRUD lifecycles) for the first time against whatever driver `phpunit.xml`
points at. If it points at SQLite (the Phase 1 default — see README §10), run
it once there for a fast pass, then run it again with `DB_CONNECTION=mysql`
in the test environment to catch anything SQLite's looser SQL-mode hides
(this is exactly how the three real bugs mentioned below first surfaced).

## 2. Manual smoke-test checklist

Automated tests cover units and authorization boundaries; this checklist
walks one real business transaction all the way through the system, the way
an actual user would, across all three portals. Do this once after a fresh
`migrate --seed` before trusting a deployment.

- [ ] **Log in as the seeded super admin** at `/login` with the credential
      `SuperAdminSeeder` printed to the console during seeding. Confirm the
      dashboard loads and every KPI card renders without a 500 error (each
      card is gated on a different permission — the super admin should see
      all of them).
- [ ] **CRM: create a Lead, convert it to a Client.** `Leads → Create`, fill
      the form, save. Open the lead, change its status through the pipeline
      to `Won`, and confirm the conversion creates a `Client` record you can
      find under `Clients`.
- [ ] **Create a Project** for that client (`Projects → Create`), and assign
      a Project Manager. Confirm it appears on the dashboard's active-projects
      count and that `Projects → Show` loads without an N+1 warning in
      `debugbar`/the query log (Phase 12's audit checked every Index page's
      eager-loading by hand, but a live query log is the real proof).
- [ ] **Walk one Quotation through to an Invoice**: `Boqs → Create` for the
      project, `Quotations → Create` from that BOQ, send it, mark it Won,
      generate a `Contract`, then `Invoices → Create` against the contract.
      Record a `Payment` against the invoice and confirm the outstanding
      balance recalculates correctly.
- [ ] **Log in as a client portal user** at `/client/login` (link a seeded
      or test client to a `user_type=client` user first if none exists yet).
      Confirm the client sees only their own project, quotation and invoice —
      never anyone else's — and that none of the portal pages 403 (Phase 11's
      and Phase 12's audits both confirmed portal pages use a plain ownership
      check, never a staff permission, but only a live login proves it).
- [ ] **Log in as a vendor portal user** at `/vendor/login` the same way.
      Confirm the vendor sees only their own purchase orders and ledger rows.
- [ ] **Exercise one file upload** (a Product image, a Design revision, or a
      Site Visit photo) and confirm it's rejected when you deliberately pick
      a disallowed file type or an oversized file, per `UPLOAD_MAX_SIZE_KB`/
      `UPLOAD_ALLOWED_MIMES` — this proves `FileService`'s validation is
      actually wired up, not just present in the code.
- [ ] **Open each of the five Reports pages** (`Reports → Project Status /
      Sales Funnel / Financial Summary / Inventory / HR`) and confirm each
      renders and its CSV export downloads with the right columns.

If every box above checks out, all 12 phases are validated against a real
database for the first time. Please report back anything that fails —
especially a migration error, a permission string that doesn't exist, or a
page that 403s unexpectedly — these are exactly the three categories of bug
this sandbox could never catch on its own.
