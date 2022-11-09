<?php

#
# Copyright (C) 2022 Nethesis S.r.l.
# SPDX-License-Identifier: AGPL-3.0-or-later
#

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

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
        $this->execProcess(['wait-for', '-t', '30', config('database.connections.mysql.host').':'.config('database.connections.mysql.port')]);
        $this->info('Cheching if the redis is ready');
        $this->execProcess(['wait-for', '-t', '30', config('database.redis.cache.host').':'.config('database.redis.cache.port')]);
        $this->info('Copying public folder contents to web container...');
        $this->execProcess(['cp', '-r', 'public', '/app']);
        $this->info('Setting up Laravel');
        $this->call('config:cache');
        $this->execProcess(['su', '-s', '/bin/sh', '-c', 'php artisan view:cache', 'www-data']);
        $this->call('storage:link');
        $this->info('Migrating database');
        $this->call('migrate', [
            '--force' => true
        ]);
        $this->info('Setting up Application');
        $this->execProcess(['su', '-s', '/bin/sh', '-c', 'php artisan app:geoip:download', 'www-data']);
        $this->info('Setup completed, exiting.');
        return 0;
    }

    /**
     * @param array<String> $command
     */
    private function execProcess(array $command): void
    {
        $process = new Process($command);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
