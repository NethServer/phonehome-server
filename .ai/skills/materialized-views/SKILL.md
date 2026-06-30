---
name: materialized-views
description: Create and refresh PostgreSQL materialized views in this Laravel app — persist computed data via a migration (no Eloquent model), refresh it with a queued Job dispatched from the scheduler. Use when adding a materialized view, a view refresh job, or scheduling a view refresh.
---

# Materialized Views

Persist computed data in Postgres as a **materialized view**. The view is read with raw queries (`DB::table` / `DB::select`), **not** through an Eloquent model. It is created via a **migration** and refreshed by a **dispatchable Job** run from the **scheduler**.

Stack: Laravel 13, PostgreSQL (`pgsql`, `ext-pdo_pgsql`). Existing plain views live in `database/migrations/*_view.php` and use raw `DB::statement` — match that style. No model, no `Schema::create`, raw SQL only.

## When to use this skill

Use when the task involves: adding a materialized view, writing a job that refreshes a view, or scheduling that refresh. Do **not** create an Eloquent model for the view.

## 1. Create the migration

```bash
php artisan make:migration create_<name>_materialized_view
```

Edit the generated file. Postgres does **not** support `CREATE OR REPLACE` for materialized views — use `CREATE MATERIALIZED VIEW`.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE MATERIALIZED VIEW <name> AS
            SELECT
                ...your query...
            WITH DATA;
        ");

        // Required for REFRESH ... CONCURRENTLY (see step 2).
        // Must uniquely identify each row.
        DB::statement("
            CREATE UNIQUE INDEX <name>_unique_idx
            ON <name> (<unique_column_or_columns>);
        ");
    }

    public function down(): void
    {
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS <name>');
    }
};
```

- `WITH DATA` populates immediately at migration time. `WITH NO DATA` leaves it empty until first refresh (querying before first refresh then errors).
- UNIQUE index is mandatory for `REFRESH CONCURRENTLY`. Skip only if concurrent refresh not needed.

Run: `php artisan migrate`

## 2. Create the refresh Job

```bash
php artisan make:job Refresh<Name>MaterializedView
```

```php
<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class Refresh<Name>MaterializedView implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        // CONCURRENTLY = no read lock during refresh. Requires UNIQUE index.
        DB::statement('REFRESH MATERIALIZED VIEW CONCURRENTLY <name>');
    }
}
```

- `CONCURRENTLY` keeps the view readable during refresh; requires the UNIQUE index from step 1. Drop the keyword if no unique index.
- `implements ShouldQueue` dispatches to the queue worker rather than running inline.

## 3. Schedule the Job

Laravel 13 schedules in `routes/console.php` via the `Schedule` facade.

```php
use App\Jobs\Refresh<Name>MaterializedView;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new Refresh<Name>MaterializedView)
    ->hourly()                 // pick cadence: ->everyFiveMinutes(), ->daily(), ->cron('...')
    ->withoutOverlapping();    // skip if previous refresh still running
```

`->job()` dispatches onto the queue. Pick cadence by how stale the data may get.

## Gotchas

- **No `OR REPLACE`** for materialized views. To change the definition: new migration with `DROP MATERIALIZED VIEW` + `CREATE`.
- **`CONCURRENTLY` requires a UNIQUE index** and cannot run inside a transaction doing other view ops. A standalone `DB::statement` is fine.
- **First refresh on `WITH NO DATA`** cannot use `CONCURRENTLY` — the initial populate must be a plain `REFRESH MATERIALIZED VIEW <name>`. Either create `WITH DATA`, or run one non-concurrent refresh before relying on concurrent refreshes.
- **Reverting**: `down()` must `DROP MATERIALIZED VIEW IF EXISTS` (the index drops with it).
- Replace every `<name>` / `<Name>` placeholder.
