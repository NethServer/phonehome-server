<?php

//
// Copyright (C) 2022 Nethesis S.r.l.
// SPDX-License-Identifier: AGPL-3.0-or-later
//

namespace App\Console\Commands;

use App\Logic\GeoIpLocator;
use Exception;
use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
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
     */
    public function handle(GeoIpLocator $geoIpLocator): int
    {
        try {
            $country = $geoIpLocator->locate(strval($this->argument('ip')));
            $this->info('Location: '.$country->name.' ('.$country->isoCode.')');
        } catch (Exception $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
