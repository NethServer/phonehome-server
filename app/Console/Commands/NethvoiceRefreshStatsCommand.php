<?php

//
// Copyright (C) 2026 Nethesis S.r.l.
// SPDX-License-Identifier: AGPL-3.0-or-later
//

namespace App\Console\Commands;

use App\Jobs\RefreshNethvoiceStatsMaterializedViews;
use Illuminate\Console\Command;

class NethvoiceRefreshStatsCommand extends Command
{
    protected $signature = 'nethvoice:refresh-stats';

    protected $description = 'Refresh the nethvoice_* stats materialized views';

    public function handle(): int
    {
        $count = count(RefreshNethvoiceStatsMaterializedViews::VIEWS);
        $this->info(date('Y-m-d\TH:i:s')." Refreshing {$count} nethvoice materialized views...");
        dispatch_sync(new RefreshNethvoiceStatsMaterializedViews);
        $this->info(date('Y-m-d\TH:i:s').' Done.');

        return self::SUCCESS;
    }
}
