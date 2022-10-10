<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use tronovav\GeoIP2Update\Client as GeoIp2UpdateClient;

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
    public function handle()
    {
        $geoIPToken = Config::get('geoip.geoip_token');
        if (is_null($geoIPToken)) {
            $this->error('No GeoLite2 token set in configuration.');
            return Command::FAILURE;
        }

        // Careful, this library is not so great, be sure to test it very well.
        $client = new GeoIp2UpdateClient(array(
            'license_key' => Config::get('geoip.geoip_token'),
            'dir' => Config::get('geoip.geoip_directory'),
            'editions' => array('GeoLite2-Country'),
        ));
        $client->run();

        $exitStatus = Command::SUCCESS;

        if (!empty($client->errors())) {
            foreach ($client->errors() as $error) {
                $this->error('Failed to update ' . $error);
            }
            $exitStatus = Command::FAILURE;
        }

        foreach ($client->updated() as $updated) {
            $this->info($updated . ' updated successfully.');
        }

        return $exitStatus;
    }
}
