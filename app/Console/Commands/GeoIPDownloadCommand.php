<?php

#
# Copyright (C) 2022 Nethesis S.r.l.
# SPDX-License-Identifier: AGPL-3.0-or-later
#

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use tronovav\GeoIP2Update\Client as GeoIp2UpdateClient;

/**
 * @codeCoverageIgnore
 */
class GeoIPDownloadCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:geoip:download';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download latest GeoLite2 Country database.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $geoIPToken = Config::get('geoip.geoip_token');
        if (is_null($geoIPToken)) {
            $this->error('No GeoLite2 token set in configuration.');
            return self::FAILURE;
        }

        // Careful, this library is not so great, be sure to test it very well.
        $client = new GeoIp2UpdateClient(array(
            'license_key' => Config::get('geoip.geoip_token'),
            'dir' => Config::get('geoip.geoip_directory'),
            'editions' => array('GeoLite2-Country'),
        ));
        $client->run();

        $exitStatus = self::SUCCESS;

        if (!empty($client->errors())) {
            foreach ($client->errors() as $error) {
                $this->error($error);
            }
            $exitStatus = self::FAILURE;
        }

        foreach ($client->updated() as $updated) {
            $this->info($updated);
        }

        return $exitStatus;
    }
}
