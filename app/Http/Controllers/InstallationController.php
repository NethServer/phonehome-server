<?php

//
// Copyright (C) 2022 Nethesis S.r.l.
// SPDX-License-Identifier: AGPL-3.0-or-later
//

namespace App\Http\Controllers;

use App\Http\Requests\StoreInstallationRequest;
use App\Logic\GeoIpLocator;
use App\Models\Country;
use App\Models\Installation;
use GeoIp2\Exception\AddressNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class InstallationController extends Controller
{
    /**
     * Store a new Installation.
     */
    public function store(StoreInstallationRequest $request, GeoIpLocator $geoIpLocator): JsonResponse
    {
        try {
            $countryRecord = $geoIpLocator->locate($request->ip() ?: '');
        } catch (AddressNotFoundException) {
            Log::error('Couldn\'t resolve location for: '.$request->ip().' ('.$request->input('uuid').')');
            throw new UnprocessableEntityHttpException();
        }

        $country = Country::firstOrCreate([
            'code' => $countryRecord->isoCode,
        ], [
            'name' => $countryRecord->name,
        ]);

        $installation = Installation::firstOrNew([
            'data->uuid' => $request->input('uuid'),
        ]);

        if ($installation->exists) {
            $installation->touch();
        }

        $data = $installation->data;
        $data['installation'] = $request->input('installation');
        $data['facts'] = $request->input('facts');
        $installation->data = $data;

        $installation->country()->associate($country);
        $installation->save();

        return response()->json(status: 201);
    }
}
