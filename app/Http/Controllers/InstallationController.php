<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInstallationRequest;
use App\Logic\GeoIpLocator;
use App\Models\Installation;
use GeoIp2\Exception\AddressNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class InstallationController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreInstallationRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreInstallationRequest $request, GeoIpLocator $geoIpLocator): JsonResponse
    {
        $installation = new Installation($request->validated());
        $installation->source_ip = $request->ip();
        try {
            $installation->country_iso_code = $geoIpLocator->locate($request->ip())->isoCode;
        } catch (AddressNotFoundException) {
            Log::error("Couldn't resolve location for: " . $request->ip());
            throw new UnprocessableEntityHttpException();
        }
        $installation->save();
        return response()->json();
    }
}
