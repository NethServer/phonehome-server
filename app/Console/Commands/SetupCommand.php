<?php

#
# Copyright (C) 2022 Nethesis S.r.l.
# SPDX-License-Identifier: AGPL-3.0-or-later
#

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup application for production, careful, this is intended to be executed inside the official container.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Waiting for database to come up');
        shell_exec('wait-for -t 30 '.config('database.connections.mysql.host').':'.config('database.connections.mysql.port'));
        $this->info('Cheching if the redis is ready');
        shell_exec('wait-for -t 30 '.config('database.redis.cache.host').':'.config('database.redis.cache.port'));
        $this->info('Copying public folder contents to web container...');
        shell_exec('cp -r public /app');
        $this->info('Setting up Laravel');
        $this->callSilently('config:cache');
        $this->callSilently('view:cache');
        $this->callSilently('storage:link');
        $this->info('Migrating database');
        $this->callSilently('migrate', [
            '--force' => true
        ]);
        $this->info('Setting up Application');
        $this->callSilently('app:geoip:download');
        $this->info('Setup completed, exiting.');
        return 0;
    }
}
