<?php

namespace App\Providers;

use App\Logic\GeoIpLocator;
use App\Logic\GeoIpLocatorImpl;
use Illuminate\Support\ServiceProvider;

class GeoIpProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(GeoIpLocator::class, function ($app) {
            return new GeoIpLocatorImpl(config('geoip.geoip_directory'));
        });
    }

}
