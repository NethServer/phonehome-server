<?php

return [

    /*
    |--------------------------------------------------------------------------
    | GeoLite2 Token
    |--------------------------------------------------------------------------
    |
    | Licence key needed to retrieve the lastest GeoLite2 Country database.
    | To get yours, simply register to:
    | https://dev.maxmind.com/geoip/geolite2-free-geolocation-data
    |
    */

    'geoip_token' => env('GEOIP_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | GeoLite2 Storage configuration options
    |--------------------------------------------------------------------------
    |
    | Directory to save in the updated database. You will most likely not need to use this.
    |
    */
    'geoip_directory' => env('GEOIP_DIRECTORY', 'storage/app')

];
