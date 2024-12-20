<?php

return [
    /*
     * --------------------------------------------------------------------------
     * Grafana username used to grant access to the database.
     * --------------------------------------------------------------------------
     */
    'database_username' => env('GRAFANA_DATABASE_USERNAME'),
    /*
     * --------------------------------------------------------------------------
     * Grafana password used to grant access to the database.
     * --------------------------------------------------------------------------
     */
    'database_password' => env('GRAFANA_DATABASE_USERNAME'),
    /*
     * --------------------------------------------------------------------------
     * Grafana public dashboard redirect.
     * --------------------------------------------------------------------------
     */
    'public_dashboard_redirect' => env('GRAFANA_PUBLIC_DASHBOARD_REDIRECT'),
];
