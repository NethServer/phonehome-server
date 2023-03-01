<?php

//
// Copyright (C) 2022 Nethesis S.r.l.
// SPDX-License-Identifier: AGPL-3.0-or-later
//

namespace App\Http\Controllers;

use App\Http\Requests\IndexInstallationRequest;
use App\Http\Requests\StoreInstallationRequest;
use App\Logic\GeoIpLocator;
use App\Models\Country;
use App\Models\Installation;
use GeoIp2\Exception\AddressNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class InstallationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexInstallationRequest $request): JsonResponse
    {
        // retro-compatible query
        $query = DB::table('countries')
            ->selectRaw('countries.name as country_name, countries.code as country_code, installations.data->\'facts\'->>\'version\' as tag, COUNT(installations.data->>\'uuid\') as num')
            ->join('installations', 'installations.country_id', '=', 'countries.id')
            ->whereRaw('data->>\'installation\' = \'nethserver\'')
            ->whereRaw('data->\'facts\'->\'version\' is not null');
        if ($request->get('interval') != '1') {
            $query = $query->whereRaw('installations.updated_at > \''.today()->subDays($request->get('interval'))->toDateString().'\'');
        }
        $query = $query->groupBy('countries.name', 'countries.code', 'tag')
            ->orderBy('installations.data->facts->version');
        $query = DB::table(DB::raw('('.$query->toSql().') as base'))
            ->select('country_name', 'country_code', DB::raw('array_to_string(array_agg(concat( tag, \'#\', num )), \',\') AS installations'))
            ->groupBy('country_name', 'country_code')
            ->orderBy('country_code');

        return response()->json(
            $query->get()
        );
    }

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
