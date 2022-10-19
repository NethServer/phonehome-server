<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexInstallationRequest;
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
    public function index(IndexInstallationRequest $request): JsonResponse
    {
        // retro-compatible query
        $query = DB::table('countries')
            ->selectRaw('countries.name as country_name, countries.code as country_code, versions.tag, COUNT(installations.uuid) as num')
            ->join('installations', 'installations.country_id', '=', 'countries.id')
            ->join('versions', 'versions.id', '=', 'installations.version_id');
        if ($request->get('interval') != '1') {
            $query = $query->whereRaw('installations.updated_at > "' . today()->subDays($request->get('interval'))->toDateString() . '"');
        }
        $query = $query->groupBy('countries.name', 'countries.code', 'versions.tag')
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
        // Search if same UUID sent a request before
        $installation = Installation::firstOrNew([
            'uuid' => $request->get('uuid')
        ]);

        // if record exists, update the timestamp
        if ($installation->exists()) {
            $installation->touch();
        }

        // Apply type from request
        $installation->type = $request->get('type');

        // Locate IP
        try {
            $countryRecord = $geoIpLocator->locate($request->ip());
        } catch (AddressNotFoundException) {
            Log::error("Couldn't resolve location for: " . $request->ip() . '(' . $request->get('uuid') . ')');
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
