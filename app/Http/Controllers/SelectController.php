<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SelectController extends Controller
{

    public function selectHardware(Request $request)
    {
        $hardwareType = $request->input('hardware_type');
        $request->session()->put('hardwareType', $hardwareType);
        return view('hardware', ['hardware_type' => $hardwareType]);
    }

    public function getHardwareType(Request $request){
        $hardwareType = $request->session()->get('hardwareType');
        return $hardwareType;
    }
}
