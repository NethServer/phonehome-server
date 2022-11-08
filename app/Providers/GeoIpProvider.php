<?php

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
            return new Reader(storage_path('app/GeoLite2-Country/GeoLite2-Country.mmdb'));
        });

        $this->app->singleton(GeoIpLocator::class, function (Container $app) {
            return new GeoIpLocatorImpl($app->make(Reader::class));
        });
    }
}
