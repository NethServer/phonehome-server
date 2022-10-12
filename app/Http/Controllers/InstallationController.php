<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInstallationRequest;
use App\Logic\GeoIpLocator;
use App\Models\Installation;

class InstallationController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreInstallationRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreInstallationRequest $request, GeoIpLocator $geoIpLocator)
    {
        $installation = new Installation($request->validated());
        $installation->source_ip = $request->ip();
        $installation->country_iso_code = $geoIpLocator->locate($request->ip());
        $installation->save();
        return response()->json([], 200);
    }
}
