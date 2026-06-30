<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Flattened nethvoice_* stats as materialized views so Metabase/Grafana can
     * query them cheaply (no live JSONB unpacking). The data is refreshed nightly
     * by RefreshNethvoiceStatsMaterializedViews (scheduled nightly via App\Console\Kernel).
     *
     * Raw SQL on purpose: these are JSONB-heavy materialized views the schema
     * builder cannot express. The `nethvoice_*_stats` views read from the
     * `nethvoice_installations` view, so it must be created first.
     */
    public function up(): void
    {
        // ========================================================
        // nethvoice_installations
        // One row per nethvoice module installation (last 7 days).
        // ========================================================
        DB::statement("
            CREATE MATERIALIZED VIEW nethvoice_installations AS
            SELECT
                i.id                                                            AS installation_id,
                i.country_id,
                c.code                                                          AS country_code,
                i.updated_at,
                m ->> 'version'                                                 AS module_version,
                (i.data::jsonb -> 'facts' -> 'cluster' ->> 'subscription')      AS subscription,

                -- counts
                (m ->> 'nethvoice_users_count')::int                            AS users_count,
                (m ->> 'nethvoice_trunks_count')::int                           AS trunks_count,
                (m ->> 'nethvoice_inbound_routes_count')::int                   AS inbound_routes_count,
                (m ->> 'nethvoice_outbound_routes_count')::int                  AS outbound_routes_count,
                (m ->> 'nethvoice_ivr_count')::int                              AS ivr_count,
                (m ->> 'nethvoice_queues_count')::int                           AS queues_count,
                (m ->> 'nethvoice_ringgroups_count')::int                       AS ringgroups_count,
                (m ->> 'nethvoice_cqr_count')::int                              AS cqr_count,
                (m ->> 'nethvoice_calls_last_24h')::int                         AS calls_last_24h,
                (m ->> 'nethvoice_total_calls')::int                            AS total_calls,
                (m ->> 'nethvoice_cti_profiles_count')::int                     AS cti_profiles_count,
                (m ->> 'nethvoice_cti_groups_count')::int                       AS cti_groups_count,
                (m ->> 'nethvoice_cti_users_count')::int                        AS cti_users_count,
                (m ->> 'nethvoice_cti_2fa_enabled_count')::int                  AS cti_2fa_enabled_count,
                (m ->> 'nethvoice_streaming_count')::int                        AS streaming_count,
                (m ->> 'nethvoice_paramurl_count')::int                         AS paramurl_count,
                (m ->> 'nethvoice_announcements_count')::int                    AS announcements_count,
                (m ->> 'nethvoice_offhour_count')::int                          AS offhour_count,
                (m ->> 'nethvoice_customer_cards_count')::int                   AS customer_cards_count,
                (m ->> 'nethvoice_nethlink_active_count')::int                  AS nethlink_active_count,
                (m ->> 'nethvoice_devices_count')::int                          AS devices_count,
                (m -> 'nethvoice_devices_by_type' ->> 'mobile')::int            AS mobile_devices_count,
                (m -> 'nethvoice_devices_by_type' ->> 'webrtc')::int            AS webrtc_devices_count,
                (m -> 'nethvoice_devices_by_type' ->> 'physical')::int          AS physical_devices_count,
                (m -> 'nethvoice_devices_by_type' ->> 'nethlink')::int          AS nethlink_devices_count,

                -- booleans
                (m ->> 'nethvoice_hotel_enabled')::boolean                      AS hotel_enabled,
                (m ->> 'nethvoice_ai_call_summary_enabled')::boolean            AS ai_call_summary_enabled,
                (m ->> 'nethvoice_ai_call_transcription_enabled')::boolean      AS ai_call_transcription_enabled,
                (m ->> 'nethvoice_ai_voicemail_transcription_enabled')::boolean AS ai_voicemail_transcription_enabled,
                (m ->> 'nethvoice_subscription_enabled')::boolean               AS subscription_enabled,

                -- strings
                m ->> 'nethvoice_user_domain_type'                             AS user_domain_type,
                m ->> 'nethvoice_user_domain_location'                         AS user_domain_location

            FROM installations i
            LEFT JOIN countries c ON i.country_id = c.id,
                LATERAL jsonb_array_elements((i.data::jsonb -> 'facts' -> 'modules')) AS m
            WHERE m ->> 'module' = 'nethvoice'
                AND jsonb_exists(i.data::jsonb -> 'facts', 'modules')
                AND i.updated_at::date >= NOW()::date - INTERVAL '7 days';
        ");
        DB::statement('CREATE INDEX ON nethvoice_installations (installation_id)');
        DB::statement('CREATE INDEX ON nethvoice_installations (module_version)');
        DB::statement('CREATE INDEX ON nethvoice_installations (updated_at)');
        DB::statement('CREATE INDEX ON nethvoice_installations (users_count)');
        DB::statement('CREATE INDEX ON nethvoice_installations (calls_last_24h)');
        DB::statement('CREATE INDEX ON nethvoice_installations (inbound_routes_count)');
        DB::statement('CREATE INDEX ON nethvoice_installations (cqr_count)');
        DB::statement('CREATE INDEX ON nethvoice_installations (country_code)');
        DB::statement('CREATE INDEX ON nethvoice_installations (subscription)');

        // ========================================================
        // nethvoice_module_status (fast installed/not_installed queries)
        // ========================================================
        DB::statement("
            CREATE MATERIALIZED VIEW nethvoice_module_status AS
            SELECT
                i.id AS installation_id,
                EXISTS (
                    SELECT 1 FROM jsonb_array_elements((i.data::jsonb -> 'facts' -> 'modules')) AS m
                    WHERE m ->> 'module' = 'nethvoice'
                ) AS has_nethvoice
            FROM installations i
            WHERE i.updated_at::date >= NOW()::date - INTERVAL '7 days'
                AND jsonb_exists(i.data::jsonb -> 'facts', 'modules');
        ");
        DB::statement('CREATE INDEX ON nethvoice_module_status (has_nethvoice)');

        // ========================================================
        // nethvoice_trunks_by_tech
        // ========================================================
        DB::statement("
            CREATE MATERIALIZED VIEW nethvoice_trunks_by_tech AS
            SELECT i.id AS installation_id, kv.key AS tech, kv.value::int AS trunk_count
            FROM installations i,
                LATERAL jsonb_array_elements((i.data::jsonb -> 'facts' -> 'modules')) AS m,
                LATERAL jsonb_each_text(m -> 'nethvoice_trunks_by_tech') AS kv
            WHERE m ->> 'module' = 'nethvoice'
                AND jsonb_exists(i.data::jsonb -> 'facts', 'modules')
                AND i.updated_at::date >= NOW()::date - INTERVAL '7 days'
                AND jsonb_typeof(m -> 'nethvoice_trunks_by_tech') = 'object';
        ");
        DB::statement('CREATE INDEX ON nethvoice_trunks_by_tech (tech)');

        // ========================================================
        // nethvoice_trunks_by_provider
        // ========================================================
        DB::statement("
            CREATE MATERIALIZED VIEW nethvoice_trunks_by_provider AS
            SELECT i.id AS installation_id, kv.key AS provider, kv.value::int AS trunk_count
            FROM installations i,
                LATERAL jsonb_array_elements((i.data::jsonb -> 'facts' -> 'modules')) AS m,
                LATERAL jsonb_each_text(m -> 'nethvoice_trunks_by_provider') AS kv
            WHERE m ->> 'module' = 'nethvoice'
                AND jsonb_exists(i.data::jsonb -> 'facts', 'modules')
                AND i.updated_at::date >= NOW()::date - INTERVAL '7 days'
                AND jsonb_typeof(m -> 'nethvoice_trunks_by_provider') = 'object';
        ");
        DB::statement('CREATE INDEX ON nethvoice_trunks_by_provider (provider)');

        // ========================================================
        // nethvoice_devices_by_type
        // ========================================================
        DB::statement("
            CREATE MATERIALIZED VIEW nethvoice_devices_by_type AS
            SELECT i.id AS installation_id, kv.key AS device_type, kv.value::int AS device_count
            FROM installations i,
                LATERAL jsonb_array_elements((i.data::jsonb -> 'facts' -> 'modules')) AS m,
                LATERAL jsonb_each_text(m -> 'nethvoice_devices_by_type') AS kv
            WHERE m ->> 'module' = 'nethvoice'
                AND jsonb_exists(i.data::jsonb -> 'facts', 'modules')
                AND i.updated_at::date >= NOW()::date - INTERVAL '7 days'
                AND jsonb_typeof(m -> 'nethvoice_devices_by_type') = 'object';
        ");
        DB::statement('CREATE INDEX ON nethvoice_devices_by_type (device_type)');

        // ========================================================
        // nethvoice_physical_devices_by_vendor
        // ========================================================
        DB::statement("
            CREATE MATERIALIZED VIEW nethvoice_physical_devices_by_vendor AS
            SELECT i.id AS installation_id, kv.key AS vendor, kv.value::int AS device_count
            FROM installations i,
                LATERAL jsonb_array_elements((i.data::jsonb -> 'facts' -> 'modules')) AS m,
                LATERAL jsonb_each_text(m -> 'nethvoice_physical_devices_by_vendor') AS kv
            WHERE m ->> 'module' = 'nethvoice'
                AND jsonb_exists(i.data::jsonb -> 'facts', 'modules')
                AND i.updated_at::date >= NOW()::date - INTERVAL '7 days'
                AND jsonb_typeof(m -> 'nethvoice_physical_devices_by_vendor') = 'object';
        ");
        DB::statement('CREATE INDEX ON nethvoice_physical_devices_by_vendor (vendor)');

        // ========================================================
        // nethvoice_physical_devices_by_model
        // ========================================================
        DB::statement("
            CREATE MATERIALIZED VIEW nethvoice_physical_devices_by_model AS
            SELECT i.id AS installation_id, kv.key AS model, kv.value::int AS device_count
            FROM installations i,
                LATERAL jsonb_array_elements((i.data::jsonb -> 'facts' -> 'modules')) AS m,
                LATERAL jsonb_each_text(m -> 'nethvoice_physical_devices_by_model') AS kv
            WHERE m ->> 'module' = 'nethvoice'
                AND jsonb_exists(i.data::jsonb -> 'facts', 'modules')
                AND i.updated_at::date >= NOW()::date - INTERVAL '7 days'
                AND jsonb_typeof(m -> 'nethvoice_physical_devices_by_model') = 'object';
        ");
        DB::statement('CREATE INDEX ON nethvoice_physical_devices_by_model (model)');

        // ========================================================
        // nethvoice_proxy_installations
        // ========================================================
        DB::statement("
            CREATE MATERIALIZED VIEW nethvoice_proxy_installations AS
            SELECT
                i.id AS installation_id,
                i.country_id,
                c.code AS country_code,
                i.updated_at,
                m ->> 'version' AS module_version
            FROM installations i
            LEFT JOIN countries c ON i.country_id = c.id,
                LATERAL jsonb_array_elements((i.data::jsonb -> 'facts' -> 'modules')) AS m
            WHERE m ->> 'module' = 'nethvoice-proxy'
                AND jsonb_exists(i.data::jsonb -> 'facts', 'modules')
                AND i.updated_at::date >= NOW()::date - INTERVAL '7 days';
        ");
        DB::statement('CREATE INDEX ON nethvoice_proxy_installations (module_version)');
        DB::statement('CREATE INDEX ON nethvoice_proxy_installations (updated_at)');
        DB::statement('CREATE INDEX ON nethvoice_proxy_installations (country_code)');

        // ========================================================
        // nethvoice_version_stats (pre-aggregated version counts)
        // ========================================================
        DB::statement('
            CREATE MATERIALIZED VIEW nethvoice_version_stats AS
            SELECT
                module_version,
                COUNT(*) AS installation_count
            FROM nethvoice_installations
            GROUP BY module_version
            ORDER BY installation_count DESC;
        ');
        DB::statement('CREATE INDEX ON nethvoice_version_stats (module_version)');

        // ========================================================
        // nethvoice_country_stats (pre-aggregated by country)
        // ========================================================
        DB::statement('
            CREATE MATERIALIZED VIEW nethvoice_country_stats AS
            SELECT
                country_code,
                COUNT(DISTINCT installation_id) AS installation_count
            FROM nethvoice_installations
            WHERE country_code IS NOT NULL
            GROUP BY country_code
            ORDER BY installation_count DESC;
        ');
        DB::statement('CREATE INDEX ON nethvoice_country_stats (country_code)');

        // ========================================================
        // nethvoice_country_subscription_stats (by country & subscription)
        // ========================================================
        DB::statement('
            CREATE MATERIALIZED VIEW nethvoice_country_subscription_stats AS
            SELECT
                country_code,
                subscription,
                COUNT(DISTINCT installation_id) AS installation_count
            FROM nethvoice_installations
            WHERE country_code IS NOT NULL AND subscription IS NOT NULL
            GROUP BY country_code, subscription
            ORDER BY installation_count DESC;
        ');
        DB::statement('CREATE INDEX ON nethvoice_country_subscription_stats (country_code, subscription)');

        // ========================================================
        // nethvoice_device_type_stats (pre-aggregated device stats)
        // ========================================================
        DB::statement("
            CREATE MATERIALIZED VIEW nethvoice_device_type_stats AS
            SELECT
                'average_users'::text AS metric,
                ROUND(AVG(users_count)::numeric, 2) AS value
            FROM nethvoice_installations
            WHERE users_count IS NOT NULL
            UNION ALL
            SELECT 'max_users', MAX(users_count)::numeric FROM nethvoice_installations
            UNION ALL
            SELECT 'average_mobile_devices', ROUND(AVG(mobile_devices_count)::numeric, 2) FROM nethvoice_installations WHERE mobile_devices_count IS NOT NULL
            UNION ALL
            SELECT 'max_mobile_devices', MAX(mobile_devices_count)::numeric FROM nethvoice_installations
            UNION ALL
            SELECT 'average_webrtc_devices', ROUND(AVG(webrtc_devices_count)::numeric, 2) FROM nethvoice_installations WHERE webrtc_devices_count IS NOT NULL
            UNION ALL
            SELECT 'max_webrtc_devices', MAX(webrtc_devices_count)::numeric FROM nethvoice_installations
            UNION ALL
            SELECT 'average_physical_devices', ROUND(AVG(physical_devices_count)::numeric, 2) FROM nethvoice_installations WHERE physical_devices_count IS NOT NULL
            UNION ALL
            SELECT 'max_physical_devices', MAX(physical_devices_count)::numeric FROM nethvoice_installations
            UNION ALL
            SELECT 'average_nethlink_devices', ROUND(AVG(nethlink_devices_count)::numeric, 2) FROM nethvoice_installations WHERE nethlink_devices_count IS NOT NULL
            UNION ALL
            SELECT 'max_nethlink_devices', MAX(nethlink_devices_count)::numeric FROM nethvoice_installations;
        ");

        // ========================================================
        // Grant read access to the Metabase user
        // ========================================================
        $metabaseUser = config('metabase.username');
        foreach (self::VIEWS as $view) {
            DB::statement("GRANT SELECT ON $view TO $metabaseUser;");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // CASCADE drops the aggregate views that read from nethvoice_installations.
        foreach (array_reverse(self::VIEWS) as $view) {
            DB::statement("DROP MATERIALIZED VIEW IF EXISTS $view CASCADE");
        }
    }

    /**
     * All materialized views, ordered so dependencies come before dependents.
     */
    private const VIEWS = [
        'nethvoice_installations',
        'nethvoice_module_status',
        'nethvoice_trunks_by_tech',
        'nethvoice_trunks_by_provider',
        'nethvoice_devices_by_type',
        'nethvoice_physical_devices_by_vendor',
        'nethvoice_physical_devices_by_model',
        'nethvoice_proxy_installations',
        'nethvoice_version_stats',
        'nethvoice_country_stats',
        'nethvoice_country_subscription_stats',
        'nethvoice_device_type_stats',
    ];
};
