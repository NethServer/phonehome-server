<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInstallationRequest;
use App\Models\Installation;

class InstallationController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreInstallationRequest $request)
    {
        $installation = new Installation($request->validated());
        // TODO: add geoip logic
        $installation->save();
    }
}
