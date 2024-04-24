<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NethsecurityPCI;
use App\Models\NethserverPCI;

class PciController extends Controller
{
    public function index(Request $request, string $installation){
        $pciSearch = $request->input('pci_search');
        $pciHardwareMatch = [];
        $count = 0;

        if($pciSearch === null || $pciSearch === ''){
            return view('hardwarePci', ['pciHardwareMatch' => collect(),
                                        'installation' => $installation,
                                        'pciSearch' => $pciSearch]);
        }

        if($installation === 'NethSecurity'){
            if(! empty($pciSearch)){
                if (preg_match('/^[0-9a-fA-F]{4}:[0-9a-fA-F]{4}$/', $pciSearch)){
                    $ids = explode(':', $pciSearch);
                    $vendorId = $ids[0];
                    $deviceId = $ids[1];
                    
                    $pciHardwareMatch = NethsecurityPCI::where('vendor_id', $vendorId)
                        ->where('device_id', $deviceId)
                        ->get();
                }else{
                    return view('hardwarePci', ['installation' => $installation, 'pciSearch' => $pciSearch])
                        -> with('error','The format of the PCI ID is incorrect. Make sure to enter a value in the correct
                                format (xxxx:xxxx), where xxxx represents a sequence of 4 hexadecimal characters.');
                }
            }
        }else if($installation === 'NethServer'){
            if(! empty($pciSearch)){
                if (preg_match('/^[0-9a-fA-F]{4}:[0-9a-fA-F]{4}$/', $pciSearch)) {
                    $ids = explode(':', $pciSearch);
                    $vendorId = $ids[0];
                    $deviceId = $ids[1];
                    
                    $pciHardwareMatch = NethserverPCI::where('vendor_id', $vendorId)
                        ->where('device_id', $deviceId)
                        ->get();
                }else {
                    return view('hardwarePci',  ['installation' => $installation, 'pciSearch' => $pciSearch])
                        -> with('error','The format of the PCI ID is incorrect. Make sure to enter a value in the correct
                                format (xxxx:xxxx), where xxxx represents a sequence of 4 hexadecimal characters.');
                }
            }
        }

        $count = $pciHardwareMatch->count();

        $pciHardware = [];
        $hardwareCounts = [];

        foreach ($pciHardwareMatch as $hardware){
            $key = $hardware->class_id . '_'
                 . $hardware->vendor_id . '_'
                 . $hardware->device_id . '_'
                 . $hardware->class_name . '_'
                 . $hardware->vendor_name . '_'
                 . $hardware->device_name . '_'
                 . $hardware->driver;

            if(array_key_exists($key, $pciHardware)){
                    $hardwareCounts[$key]++;
            }else{
                $pciHardware[$key] = $hardware;
                $hardwareCounts[$key] = 1;
            }
        }

        return view('hardwarePci', ['pciHardware' => $pciHardware,
                                    'count' => $count,
                                    'installation' => $installation,
                                    'hardwareCounts' => $hardwareCounts,
                                    'pciSearch' => $pciSearch]);
    }
}
