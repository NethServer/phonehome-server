<?php

namespace App\Logic;

interface GeoIpLocator
{

    /**
     * Locate the country where the request took place.
     *
     * @param String $ip Ip request source.
     *
     * @return String Alpha-2 Code defined in ISO 3166-1.
     */
    function locate(String $ip): String;
}
