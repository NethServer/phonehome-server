<?php

namespace App\Logic;

use GeoIp2\Database\Reader;

class GeoIpLocatorImpl implements GeoIpLocator {

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
        return $this->resolver->country($ip)->country->isoCode;
    }

}
