<?php

namespace App\Logic;

use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use MaxMind\Db\Reader\InvalidDatabaseException;

class GeoIpLocatorImpl implements GeoIpLocator
{

    private readonly Reader $resolver;

    function __construct(String $geoIpDirectory)
    {
        $this->resolver = new Reader($geoIpDirectory . '/GeoLite2-Country/GeoLite2-Country.mmdb');
    }

    /**
     * @inheritdoc
     */
    public function locate(String $ip): String
    {
        return $this->retryLocate($ip);
    }

    /**
     * Allows to try X times the recovery from a invalid database.
     *
     * @param String ip to look for in the database
     * @param int current retry of the recursion, max 3 tries
     *
     * @return String location of IP, '--' if not in database
     */
    public function retryLocate(String $ip, int $retries = 0): String
    {
        try {
            return $this->resolver->country($ip)->country->isoCode;
        } catch (AddressNotFoundException) {
            return '--';
        } catch (InvalidDatabaseException $exception) {
            $returnCode = Artisan::call('app:geoip:download');
            if ($returnCode != 0 && $retries >= 3) {
                Log::emergency("Cannot recover from invalid MaxMind DB.");
                throw $exception;
            }
            return $this->retryLocate($ip, $retries + 1);
        }
    }
}
