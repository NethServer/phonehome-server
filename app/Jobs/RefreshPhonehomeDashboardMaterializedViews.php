<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class RefreshPhonehomeDashboardMaterializedViews implements ShouldQueue
{
    use Queueable;

    /**
     * Materialized views to refresh, ordered so dependencies come first:
     * phonehome_installations must be refreshed before any aggregates that
     * read from it.
     *
     * @var string[]
     */
    public const VIEWS = [
        'phonehome_installations',
        'phonehome_nethserver8_node_versions',
        'phonehome_daily_active_counts',
    ];

    public function handle(): void
    {
        foreach (self::VIEWS as $view) {
            DB::statement("REFRESH MATERIALIZED VIEW $view");
        }
    }
}
