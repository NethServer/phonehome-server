<?php

//
// Copyright (C) 2022 Nethesis S.r.l.
// SPDX-License-Identifier: AGPL-3.0-or-later
//

namespace App\Logic;

use GeoIp2\Database\Reader;
use GeoIp2\Record\Country;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use MaxMind\Db\Reader\InvalidDatabaseException;

class GeoIpLocatorImpl implements GeoIpLocator
{
    public function __construct(private Reader $resolver) {
    }

    /**
     * {@inheritdoc}
     */
    public function locate(string $ip): Country
    {
        return $this->retryLocate($ip);
    }

    /**
     * Allows to try 3 times the recovery from a invalid database.
     *
     * @param  string  $ip to look for in the database
     * @param  int  $retries current retry of the recursion, max 3 tries
     * @return \GeoIp2\Record\Country resolved by given IP
     *
     * @throws \GeoIp2\Exception\AddressNotFoundException if IP doesn't exists in database
     */
    private function retryLocate(string $ip, int $retries = 0): Country
    {
        try {
            return $this->resolver->country($ip)->country;
        } catch (InvalidDatabaseException $exception) {
            if ($retries > 2) {
                Log::emergency('Cannot recover from invalid MaxMind DB.');
                throw $exception;
            }
            Artisan::call('app:geoip:download');

            return $this->retryLocate($ip, $retries + 1);
        }
    }
}
