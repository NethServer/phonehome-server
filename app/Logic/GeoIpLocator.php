<?php

//
// Copyright (C) 2022 Nethesis S.r.l.
// SPDX-License-Identifier: AGPL-3.0-or-later
//

namespace App\Logic;

use GeoIp2\Record\Country;

interface GeoIpLocator
{
    /**
     * Locate the country where the request took place.
     *
     * @param  string  $ip Ip request source.
     * @return \GeoIp2\Record\Country Country object resolved from ip.
     */
    public function locate(string $ip): Country;
}
