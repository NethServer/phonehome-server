<?php

//
// Copyright (C) 2026 Nethesis S.r.l.
// SPDX-License-Identifier: AGPL-3.0-or-later
//

namespace App\Console\Commands;

use App\Jobs\RefreshPhonehomeDashboardMaterializedViews;
use Illuminate\Console\Command;

class PhonehomeRefreshDashboardStatsCommand extends Command
{
    protected $signature = 'phonehome:refresh-dashboard-stats';

    protected $description = 'Refresh the phonehome_* dashboard materialized views';

    public function handle(): int
    {
        $count = count(RefreshPhonehomeDashboardMaterializedViews::VIEWS);
        $this->info(date('Y-m-d\TH:i:s')." Refreshing {$count} phonehome dashboard materialized views...");
        dispatch_sync(new RefreshPhonehomeDashboardMaterializedViews);
        $this->info(date('Y-m-d\TH:i:s').' Done.');

        return self::SUCCESS;
    }
}
