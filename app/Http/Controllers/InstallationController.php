<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInstallationRequest;
use App\Logic\GeoIpLocator;
use App\Models\Country;
use App\Models\Installation;
use App\Models\Version;
use GeoIp2\Exception\AddressNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class InstallationController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        // retro-compatible query
        $query = DB::table('countries')
            ->selectRaw('countries.name as country_name, countries.code as country_code, versions.tag, COUNT(installations.uuid) as num')
            ->join('installations', 'installations.country_id', '=', 'countries.id')
            ->join('versions', 'versions.id', '=', 'installations.version_id')
            ->groupBy('countries.name', 'countries.code', 'versions.tag')
            ->orderBy('versions.tag');
        $query = DB::table(DB::raw('(' . $query->toSql() . ') as base'))
            ->select('country_name', 'country_code', DB::raw('GROUP_CONCAT(CONCAT( tag, \'#\', num )) AS installations'))
            ->groupBy('country_name', 'country_code')
            ->orderBy('country_code');

        return response()->json(
            $query->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreInstallationRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreInstallationRequest $request, GeoIpLocator $geoIpLocator): JsonResponse
    {
        // Fill installation with data from request
        $installation = new Installation($request->validated());
        try {
            $countryRecord = $geoIpLocator->locate($request->ip());
        } catch (AddressNotFoundException) {
            Log::error("Couldn't resolve location for: " . $request->ip());
            throw new UnprocessableEntityHttpException();
        }

        // Find or create new Country and associate
        $country = Country::firstOrCreate([
            'code' => $countryRecord->isoCode
        ], [
            'name' => $countryRecord->name
        ]);
        $installation->country()->associate($country);
        $country->save();

        // Find or create new Version and associate
        $version = Version::firstOrCreate([
            'tag' => $request->get('release')
        ]);
        $installation->version()->associate($version);
        $country->save();

        // Save installation
        $installation->save();
        return response()->json();
    }
}
