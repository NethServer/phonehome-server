<?php

//
// Copyright (C) 2022 Nethesis S.r.l.
// SPDX-License-Identifier: AGPL-3.0-or-later
//

namespace App\Http\Controllers;

use App\Http\Requests\IndexInstallationRequest;
use App\Logic\GeoIpLocator;
use App\Models\Country;
use App\Models\Installation;
use GeoIp2\Exception\AddressNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Beware, this is a LEGACY controller, if you're proficient in Laravel you'll see that many things are done not
 * following Laravel howtos. To maintain retro compatibility, this was done on purpose, please refrain to change
 * this controller if you have no knowledge on how NS6-NS7 phone-home and/or the UI works.
 */
class CompatibilityController extends Controller
{
    /**
     * Display a listing of the installations, this is tricky and hard to reproduce on other DBs.
     */
    public function index(IndexInstallationRequest $request): JsonResponse
    {
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
     * Store a NS7 request to the database.
     */
    public function store(Request $request, GeoIpLocator $geoIpLocator): Response
    {
        // Create Validator instance
        $validator = Validator::make($request->all(), [
            'uuid' => 'required|uuid',
            'release' => [
                'required',
                'regex:/^\d+\.\d+\.?\d*$/m', // uses preg_match
            ],
            'type' => 'nullable|in:community,enterprise,subscription',
        ]);

        if ($validator->fails()) {
            return response(null, (new UnprocessableEntityHttpException())->getStatusCode());
        } else {
            // Search if same UUID sent a request before
            $installation = Installation::firstOrNew([
                'data->uuid' => $request->get('uuid'),
            ]);
            // if record exists, update the timestamp
            if ($installation->exists) {
                $installation->touch();
            }
            $data = $installation->data;
            // Apply type from request
            $data['installation'] = 'nethserver';
            $data['facts']['type'] = $request->get('type');
            $data['facts']['version'] = $request->get('release');
            $installation->data = $data;

            // Locate IP
            try {
                $countryRecord = $geoIpLocator->locate($request->ip() ?: '');
            } catch (AddressNotFoundException) {
                Log::error('Couldn\'t resolve location for: '.$request->ip().' ('.$request->get('uuid').')');

                return response(null, (new UnprocessableEntityHttpException())->getStatusCode());
            }

            // Find or create new Country and associate
            $country = Country::firstOrCreate([
                'code' => $countryRecord->isoCode,
            ], [
                'name' => $countryRecord->name,
            ]);
            $installation->country()->associate($country);
            $country->save();

            // Save installation
            $installation->save();
        }

        return response(null);
    }
}
