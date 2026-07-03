<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Flattened phonehome dashboard stats as materialized views so the
     * Grafana "PhoneHome" dashboard (containers/grafana/provisioning/dashboards/phonehome.json)
     * can query them cheaply (no live JSONB unpacking / generate_series over
     * the full installations table on every dashboard load). Refreshed
     * nightly by RefreshPhonehomeDashboardMaterializedViews.
     *
     * Raw SQL on purpose: these are JSONB-heavy materialized views the schema
     * builder cannot express. `phonehome_daily_active_counts` reads directly
     * from `installations` (not from `phonehome_installations`, which only
     * covers the last 7 days) because historical days need installations
     * that are no longer "currently active".
     */
    public function up(): void
    {
        // ========================================================
        // phonehome_installations
        // One row per currently active (last 7 days) installation,
        // classified into nethsecurity / nethserver8 / nethserver67.
        // ========================================================
        DB::statement("
            CREATE MATERIALIZED VIEW phonehome_installations AS
            SELECT
                t.installation_id,
                t.country_id,
                t.country_code,
                t.country_name,
                t.category,
                CASE
                    WHEN t.category = 'nethsecurity' THEN t.data -> 'facts' -> 'distro' ->> 'version'
                    WHEN t.category = 'nethserver67' THEN t.data -> 'facts' ->> 'version'
                END AS version,
                CASE
                    WHEN t.category = 'nethserver8' THEN (SELECT COUNT(*) FROM jsonb_object_keys(t.data -> 'facts' -> 'nodes'))
                END AS node_count,
                t.updated_at
            FROM (
                SELECT
                    i.id AS installation_id,
                    i.country_id,
                    c.code AS country_code,
                    c.name AS country_name,
                    i.updated_at,
                    i.data::jsonb AS data,
                    CASE
                        WHEN i.data::jsonb ->> 'installation' = 'nethsecurity' THEN 'nethsecurity'
                        WHEN i.data::jsonb ->> 'installation' = 'nethserver'
                            AND jsonb_exists(i.data::jsonb -> 'facts', 'nodes') THEN 'nethserver8'
                        ELSE 'nethserver67'
                    END AS category
                FROM installations i
                LEFT JOIN countries c ON i.country_id = c.id
                WHERE i.updated_at > NOW() - INTERVAL '7 days'
                    AND i.data::jsonb ->> 'installation' IN ('nethsecurity', 'nethserver')
            ) t;
        ");
        DB::statement('CREATE UNIQUE INDEX ON phonehome_installations (installation_id)');
        DB::statement('CREATE INDEX ON phonehome_installations (category)');
        DB::statement('CREATE INDEX ON phonehome_installations (country_code)');

        // ========================================================
        // phonehome_nethserver8_node_versions
        // One row per node, for active nethserver8 clusters.
        // ========================================================
        DB::statement("
            CREATE MATERIALIZED VIEW phonehome_nethserver8_node_versions AS
            SELECT
                i.id AS installation_id,
                c.code AS country_code,
                c.name AS country_name,
                kv.key AS node_key,
                kv.value ->> 'version' AS version
            FROM installations i
            LEFT JOIN countries c ON i.country_id = c.id,
                LATERAL jsonb_each(i.data::jsonb -> 'facts' -> 'nodes') AS kv
            WHERE i.data::jsonb ->> 'installation' = 'nethserver'
                AND jsonb_exists(i.data::jsonb -> 'facts', 'nodes')
                AND i.updated_at > NOW() - INTERVAL '7 days';
        ");
        DB::statement('CREATE UNIQUE INDEX ON phonehome_nethserver8_node_versions (installation_id, node_key)');
        DB::statement('CREATE INDEX ON phonehome_nethserver8_node_versions (country_name)');

        // ========================================================
        // phonehome_daily_active_counts
        // Per-day, per-category active install count over the last 6
        // months (the "Active Installations" timeseries panel). Reads
        // the raw installations table since old rows outside the
        // current 7-day window still count as active on past days.
        // ========================================================
        // Category/date bounds computed once per row (CTE) instead of once
        // per (day x category) join comparison — the naive CROSS JOIN version
        // re-evaluates the JSONB extraction ~3000x per row and took 15+
        // minutes against 143k installations; this version takes seconds.
        DB::statement("
            CREATE MATERIALIZED VIEW phonehome_daily_active_counts AS
            WITH categorized AS (
                SELECT
                    i.created_at::date AS created_date,
                    i.updated_at::date AS updated_date,
                    CASE
                        WHEN i.data::jsonb ->> 'installation' = 'nethsecurity' THEN 'nethsecurity'
                        WHEN i.data::jsonb ->> 'installation' = 'nethserver'
                            AND jsonb_exists(i.data::jsonb -> 'facts', 'nodes') THEN 'nethserver8'
                        ELSE 'nethserver67'
                    END AS category
                FROM installations i
                WHERE i.data::jsonb ->> 'installation' IN ('nethsecurity', 'nethserver')
            )
            SELECT
                d.day::date AS day,
                cat.category,
                COUNT(c.category) AS active_count
            FROM generate_series(
                    NOW() - '6 months'::interval,
                    NOW() - '1 day'::interval,
                    '1 day'::interval
                 ) AS d(day)
            CROSS JOIN (VALUES ('nethsecurity'), ('nethserver8'), ('nethserver67')) AS cat(category)
            LEFT JOIN categorized c
                ON c.category = cat.category
                AND c.created_date <= d.day::date
                AND c.updated_date >= d.day::date - '7 days'::interval
            GROUP BY d.day, cat.category
            ORDER BY d.day, cat.category;
        ");
        DB::statement('CREATE UNIQUE INDEX ON phonehome_daily_active_counts (day, category)');

        // ========================================================
        // Grant read access to the Grafana user (the PhoneHome
        // dashboard is served over the Grafana postgres datasource).
        // ========================================================
        $grafanaUser = config('grafana.database_username');
        foreach (self::VIEWS as $view) {
            DB::statement("GRANT SELECT ON $view TO $grafanaUser;");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // CASCADE drops the aggregate views that read from phonehome_installations.
        foreach (array_reverse(self::VIEWS) as $view) {
            DB::statement("DROP MATERIALIZED VIEW IF EXISTS $view CASCADE");
        }
    }

    /**
     * All materialized views, ordered so dependencies come before dependents.
     */
    private const VIEWS = [
        'phonehome_installations',
        'phonehome_nethserver8_node_versions',
        'phonehome_daily_active_counts',
    ];
};
