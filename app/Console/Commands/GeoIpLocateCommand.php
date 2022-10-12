<?php

namespace App\Console\Commands;

use App\Logic\GeoIpLocator;
use Exception;
use Illuminate\Console\Command;

class GeoIpLocateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:geoip:locate {ip}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lookup IP using MaxMind Database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(GeoIpLocator $geoIpLocator)
    {
        try {
            $this->info($geoIpLocator->locate($this->argument('ip')));
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
            return $this::FAILURE;
        }
        return $this::SUCCESS;
    }
}
