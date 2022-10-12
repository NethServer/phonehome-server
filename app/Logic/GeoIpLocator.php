<?php

namespace App\Logic;

use GeoIp2\Record\Country;

interface GeoIpLocator
{

    /**
     * Locate the country where the request took place.
     *
     * @param String $ip Ip request source.
     *
     * @return \GeoIp2\Record\Country Country object resolved from ip.
     */
    function locate(String $ip): Country;
}
