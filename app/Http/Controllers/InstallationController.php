<?php

//
// Copyright (C) 2022 Nethesis S.r.l.
// SPDX-License-Identifier: AGPL-3.0-or-later
//

namespace App\Http\Controllers;

use App\Http\Requests\IndexInstallationRequest;
use App\Http\Requests\StoreInstallationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

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
            ->selectRaw('countries.name as country_name, countries.code as country_code, installations.data->\'facts\'->>\'version\' as tag, COUNT(installations.data->>\'uuid\') as num')
            ->join('installations', 'installations.country_id', '=', 'countries.id');
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
     * Store a new Installation
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreInstallationRequest $request): JsonResponse
    {
        return response()->json(status: 201);
    }
}
