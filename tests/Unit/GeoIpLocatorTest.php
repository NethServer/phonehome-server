<?php

use App\Logic\GeoIpLocatorImpl;
use GeoIp2\Database\Reader;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use MaxMind\Db\Reader\InvalidDatabaseException;
use Mockery\MockInterface;

test('invalid database present', function () {
    $reader = Mockery::mock(Reader::class, function (MockInterface $mock) {
        $mock->shouldReceive('city')
            ->with('127.0.0.1')
            ->andThrow(new InvalidDatabaseException());
    });

    Artisan::partialMock()
        ->shouldReceive('call')
        ->times(3)
        ->with('geoip:update');

    Log::partialMock()
        ->shouldReceive('emergency')
        ->once()
        ->with('Cannot recover from invalid MaxMind DB.');

    $geoIpLocator = new GeoIpLocatorImpl($reader);
    $geoIpLocator->locate('127.0.0.1');
})->throws(InvalidDatabaseException::class);
