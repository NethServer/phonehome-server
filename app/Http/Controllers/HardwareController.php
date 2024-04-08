<?php

namespace App\Http\Controllers;

use App\Models\Installation;
use App\Models\Hardware;
use Illuminate\Http\Request;

class HardwareController extends Controller
{
    // Show all hardware
    public function index()
    {
        $hardware = Hardware::all();
        return response()->json($hardware);
    }

    // Show a single hardware
    public function show($id)
    {
        $hardware = Hardware::find($id);
        if (!$hardware) {
            return response()->json(['message' => 'Hardware not found'], 404);
        }
        return response()->json($hardware);
    }

    // Create a new hardware
    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required',
            'manufacturer' => 'required',
            'processor' => 'required',
            'vga_controller' => 'required',
            'usb_controller' => 'required',
            'pci_bridge' => 'required',
            'stata_controller' => 'required',
            'communication_controller' => 'required',
            'scsi_controller' => 'required',
            'ethernet' => 'required'
        ]);

        $hardware = Hardware::create($request->all());
        return response()->json($hardware, 201);
    }

    // Update an existing hardware
    public function update(Request $request, $id)
    {
        $hardware = Hardware::find($id);
        if (!$hardware) {
            return response()->json(['message' => 'Hardware not found'], 404);
        }

        $hardware->update($request->all());
        return response()->json($hardware);
    }

    // Delete an existing hardware
    public function destroy($id)
    {
        $hardware = Hardware::find($id);
        if (!$hardware) {
            return response()->json(['message' => 'Hardware not found'], 404);
        }

        $hardware->delete();
        return response()->json(['message' => 'Hardware deleted']);
    }

    public function searchHardware(Request $request)
    {
        $request->validate([
            'product_name' => 'required',
            'manufacturer' => 'required',
            'processor' => 'required',
        ]);

        $productName = $request->input('product_name');
        $manufacturer = $request->input('manufacturer');
        $processor = $request->input('processor');

        $matchingHardware = Hardware::where('product_name', $product_name)
            ->where('manufacturer', $manufacturer)
            ->where('processor', $processor)
            ->get();
        
        return view('findHardware', ['matchingHardware' => $matchingHardware]);
    }
}
