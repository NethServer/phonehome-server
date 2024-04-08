<?php

namespace App\Http\Controllers;

use App\Models\Installation;
use App\Models\Hardware;
use Illuminate\Http\Request;

class HardwareController extends Controller
{
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

    public function index(Request $request)
    {
        $searchTerm = $request->input('search_term');

        //Perform a query to find all hardware that contain the search term
        $matchingHardware = Hardware::where('product_name', 'ilike', '%' . $searchTerm . '%')
        ->orWhere('manufacturer', 'ilike', '%' . $searchTerm . '%')
        ->orWhere('processor', 'ilike', '%' . $searchTerm . '%')
        ->orWhere('vga_controller', 'ilike', '%' . $searchTerm . '%')
        ->orWhere('usb_controller', 'ilike', '%' . $searchTerm . '%')
        ->orWhere('pci_bridge', 'ilike', '%' . $searchTerm . '%')
        ->orWhere('sata_controller', 'ilike', '%' . $searchTerm . '%')
        ->orWhere('communication_controller', 'ilike', '%' . $searchTerm . '%')
        ->orWhere('scsi_controller', 'ilike', '%' . $searchTerm . '%')
        ->orWhere('ethernet', 'ilike', '%' . $searchTerm . '%')
        ->get();

        return view('hardware', ['matchingHardware' => $matchingHardware]);
    }
}
