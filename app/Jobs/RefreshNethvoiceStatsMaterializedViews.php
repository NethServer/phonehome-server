<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class RefreshNethvoiceStatsMaterializedViews implements ShouldQueue
{
    use Queueable;

    /**
     * Materialized views to refresh, ordered so dependencies come first:
     * nethvoice_installations must be refreshed before the aggregates that
     * read from it.
     *
     * @var string[]
     */
    public const VIEWS = [
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

    public function handle(): void
    {
        foreach (self::VIEWS as $view) {
            DB::statement("REFRESH MATERIALIZED VIEW $view");
        }
    }
}
