<?php

namespace App\Http\Controllers;

use App\Logic\GeoIpLocator;
use App\Models\Country;
use App\Models\Installation;
use GeoIp2\Exception\AddressNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class CompatibilityController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * Beware, this is a LEGACY request, if you're proficient in Laravel you'll see that many things are done not
     * following Laravel howtos. To maintain retro compatibility, this was done on purpose, please refrain to change
     * this function if you have no knowledge on how NS6-NS7 phone-home works.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, GeoIpLocator $geoIpLocator): Response
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
