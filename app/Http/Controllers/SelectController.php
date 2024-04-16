<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SelectController extends Controller
{

    public function selectHardware(Request $request)
    {
        $hardwareType = $request->input('hardware_type');
        // Store hardware type in session
        $request->session()->put('hardwareType', $hardwareType);
        return view('hardware', ['hardware_type' => $hardwareType]);
    }

    public function getHardwareType(Request $request){
        // Retrieve hardware type from session
        $hardwareType = $request->session()->get('hardwareType');
        return $hardwareType;
    }
}
