<?php

//
// Copyright (C) 2022 Nethesis S.r.l.
// SPDX-License-Identifier: AGPL-3.0-or-later
//

namespace App\Providers;

use App\Logic\GeoIpLocator;
use App\Logic\GeoIpLocatorImpl;
use GeoIp2\Database\Reader;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;

/**
 * @codeCoverageIgnore
 */
class GeoIpProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Reader::class, function () {
            return new Reader(storage_path('app/GeoLite2-City.mmdb'));
        });

        $this->app->singleton(GeoIpLocator::class, function (Container $app) {
            return new GeoIpLocatorImpl($app->make(Reader::class));
        });
    }
}
