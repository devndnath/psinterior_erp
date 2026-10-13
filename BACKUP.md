# Automated backups & recovery

Closes the audit's Section-F gap: before this, a database loss or a bad
migration had no recovery path beyond whatever the hosting provider's own
snapshots happened to cover — nothing the team controlled or could verify.

This uses [`spatie/laravel-backup`](https://github.com/spatie/laravel-backup),
already declared in `composer.json`. Its config is `config/backup.php` and
its schedule is in `routes/console.php`. Because `composer install` cannot
run in the build sandbox this was written in, nothing here has been
executed against a real database — treat the one-time setup below as
required, not optional.

## What gets backed up, and when

Three commands run daily on the server's cron, in this order (see
`routes/console.php`):

| Time  | Command           | What it does                                                                 |
|-------|--------------------|-------------------------------------------------------------------------------|
| 01:30 | `backup:clean`     | Prunes old archives per the retention policy below                           |
| 02:00 | `backup:run`       | Dumps the MySQL database + `storage/app/public` and `storage/app/private`    |
| 03:00 | `backup:monitor`   | Checks the latest backup isn't stale or oversized; emails on failure         |

Retention (in `config/backup.php` → `cleanup.default_strategy`):
all backups kept 7 days, then one-a-day for 30 days, one-a-week for 8
weeks, one-a-month for 12 months, one-a-year for 2 years — standard
Spatie defaults, not tuned for this project yet. Adjust the numbers there
if the business wants a different window.

## One-time setup required on the server (not done by this change)

1. **Install the package** — this change only adds the composer.json
   entry; someone with working Packagist access still has to run:
   ```
   composer install
   ```
2. **Confirm `mysqldump` is on the server's PATH.** The database dump
   shells out to it; without it, `backup:run` fails silently into the
   failure-notification path rather than corrupting anything, but no
   backup will exist until it's installed.
3. **Create the backup directory and make it writable:**
   ```
   mkdir -p storage/app/backups storage/app/backup-temp
   ```
   (Laravel's deploy step that runs `storage:link`/sets permissions on
   `storage/` should already cover this, but it's worth checking after
   first deploy.)
4. **Register the cron entry.** The existing README already documents
   the single required line for Laravel's scheduler
   (`* * * * * php artisan schedule:run`) — if that's already running in
   production, these three commands start firing with no further change.
   If it isn't yet, this is also the thing that was silently making
   Phase 11's `invoices:flag-overdue` never actually fire — set it up
   once and both features start working.
5. **Set `BACKUP_NOTIFICATION_EMAIL` in `.env`** to an address someone
   actually reads, so a failed or stale backup doesn't go unnoticed.
   Leaving it blank means failures are silently swallowed by the mail
   notification channel having no recipient.
6. **(Recommended, not yet wired up) Point a copy off-server.** The `s3`
   filesystem disk already exists in `config/filesystems.php` for this —
   fill in the `AWS_*` env vars and add `'s3'` to `destination.disks` in
   `config/backup.php`. Until that's done, a backup that only lives on
   the same server as the database it's protecting doesn't survive the
   server itself being lost (a disk failure, a bad `rm -rf`, the host
   going down) — only a same-server accidental-DROP or bad-migration
   scenario.

## Restoring from a backup

There is no `backup:restore` artisan command — Spatie's package is
backup-only by design, restoration is a manual, deliberate action so it
can never be triggered by accident.

1. **Locate the archive.** Backups land in `storage/app/backups/<app
   name>/` as timestamped `.zip` files. Pick the one just before the
   point you need to recover to.
2. **Unzip it** into a scratch directory (not back into `storage/app`
   directly — inspect first):
   ```
   unzip 2026-02-10-02-00-00.zip -d /tmp/restore
   ```
   Inside you'll find `db-dumps/mysql-<db name>.sql.gz` and the two
   storage trees (`public/`, `private/`) as plain directories.
3. **Restore the database** — take a fresh dump of the *current*
   (broken) database first, in case the restore itself needs undoing:
   ```
   mysqldump -u <user> -p <db> > /tmp/pre-restore-safety-dump.sql
   gunzip db-dumps/mysql-<db name>.sql.gz
   mysql -u <user> -p <db> < db-dumps/mysql-<db name>.sql
   ```
4. **Restore the files** by copying the unzipped `public/` and
   `private/` directories back over `storage/app/public` and
   `storage/app/private` — back up the current contents first the same
   way as step 3.
5. **Clear cached config/routes** in case anything changed:
   ```
   php artisan optimize:clear
   ```
6. **Spot-check** a project, an invoice, and a design revision in the
   app before telling anyone the recovery is done.

## What this does not cover yet

- No automated restore drill — the steps above have not been tested
  end-to-end against a real database, because this sandbox can't run
  migrations or install the package. Running a real test restore on a
  staging copy is strongly recommended before relying on this.
- No off-server copy until step 6 of the one-time setup is done.
- No point-in-time recovery between daily backups (binlog-based PITR)
  — a loss mid-day can only be rolled back to the previous night's
  backup, losing that day's data. Flagged as a known limitation, not
  something this phase attempts to solve.
