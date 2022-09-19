<?php

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
        $this->info('Setting up Laravel');
        $this->callSilently('config:cache');
        $this->callSilently('view:cache');
        $this->callSilently('storage:link');
        if (config('app.env') == 'production') {
            $this->info('Migrating database');
            $this->callSilently('migrate', [
                '--force' => true,
                '--seed' => true
            ]);
        } else {
            $this->info('Application in development mode, resetting database...');
            $this->callSilently('migrate:fresh', [
                '--force' => true,
                '--seed' => true
            ]);
        }
        $this->info('Setup completed, exiting.');
        return 0;
    }
}
