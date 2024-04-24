<?php

namespace App\Http\Controllers;

use App\Models\NethsecurityHardware;
use App\Models\NethserverHardware;
use Illuminate\Http\Request;

class HardwareController extends Controller
{
    public function index(Request $request, string $installation)
    {
        // Retrieve search term from request
        $searchTerm = $request->input('search_term');
        // Initialize variables for storing matches and count
        $matchingHardware = [];
        $inputMatch = [];
        $count = 0;

        // If search term is empty, return an empty view
        if ($searchTerm === null || $searchTerm === '') {
            return view('hardware', ['matchingHardware' => collect(), 'installation' => $installation, 'searchTerm' => $searchTerm]);
        }
        // Perform a query to find all hardware that contain the search term
        if ($installation === 'NethServer') {
            if (! empty($searchTerm)) {
                $matchingHardware = NethserverHardware::where('product_name', 'ilike', '%'.$searchTerm.'%')
                    ->orWhere('manufacturer', 'ilike', '%'.$searchTerm.'%')
                    ->orWhere('processor', 'ilike', '%'.$searchTerm.'%')
                    ->orWhere('vga_controller', 'ilike', '%'.$searchTerm.'%')
                    ->orWhere('usb_controller', 'ilike', '%'.$searchTerm.'%')
                    ->orWhere('pci_bridge', 'ilike', '%'.$searchTerm.'%')
                    ->orWhere('sata_controller', 'ilike', '%'.$searchTerm.'%')
                    ->orWhere('communication_controller', 'ilike', '%'.$searchTerm.'%')
                    ->orWhere('scsi_controller', 'ilike', '%'.$searchTerm.'%')
                    ->orWhere('ethernet', 'ilike', '%'.$searchTerm.'%')
                    ->get();
            }
        } elseif ($installation === 'NethSecurity') {
            if (! empty($searchTerm)) {
                $matchingHardware = NethsecurityHardware::where('product_name', 'ilike', '%'.$searchTerm.'%')
                    ->orWhere('manufacturer', 'ilike', '%'.$searchTerm.'%')
                    ->orWhere('processor', 'ilike', '%'.$searchTerm.'%')
                    ->orWhere('vga_controller', 'ilike', '%'.$searchTerm.'%')
                    ->orWhere('usb_controller', 'ilike', '%'.$searchTerm.'%')
                    ->orWhere('pci_bridge', 'ilike', '%'.$searchTerm.'%')
                    ->orWhere('sata_controller', 'ilike', '%'.$searchTerm.'%')
                    ->orWhere('communication_controller', 'ilike', '%'.$searchTerm.'%')
                    ->orWhere('scsi_controller', 'ilike', '%'.$searchTerm.'%')
                    ->orWhere('ethernet', 'ilike', '%'.$searchTerm.'%')
                    ->get();
            }
        }
        // Returning an array containing only the specific hardware elements that contain the search term
        foreach ($matchingHardware as $hardware) {
            if (stripos($hardware->product_name, $searchTerm) !== false) {
                $inputMatch[] = 'Product Name: '.$hardware->product_name;
            } elseif (stripos($hardware->manufacturer, $searchTerm) !== false) {
                $inputMatch[] = 'Manufacturer: '.$hardware->manufacturer;
            } elseif (stripos($hardware->processor, $searchTerm) !== false) {
                $inputMatch[] = 'Processor: '.$hardware->processor;
            } elseif (stripos($hardware->vga_controller, $searchTerm) !== false) {
                $inputMatch[] = 'Vga Controller: '.$hardware->vga_controller;
            } elseif (stripos($hardware->usb_controller, $searchTerm) !== false) {
                $inputMatch[] = 'Usb Controller: '.$hardware->usb_controller;
            } elseif (stripos($hardware->pci_bridge, $searchTerm) !== false) {
                $inputMatch[] = 'Pci Bridge: '.$hardware->pci_bridge;
            } elseif (stripos($hardware->sata_controller, $searchTerm) !== false) {
                $inputMatch[] = 'Sata Controller: '.$hardware->sata_controller;
            } elseif (stripos($hardware->communication_controller, $searchTerm) !== false) {
                $inputMatch[] = 'Communication Controller: '.$hardware->communication_controller;
            } elseif (stripos($hardware->scsi_controller, $searchTerm) !== false) {
                $inputMatch[] = 'SCSI Controller: '.$hardware->scsi_controller;
            } elseif (stripos($hardware->ethernet, $searchTerm) !== false) {
                $inputMatch[] = 'Ethernet: '.$hardware->ethernet;
            }
            $count++;
        }

        // Initialize array for grouping and count of occurrences
        $groupedInputMatch = [];
        $rowsCount = 0;

        // Loop through inputMatch to group similar rows and count occurrences
        foreach ($inputMatch as $item) {
            [$value, $row] = explode(': ', $item, 2);

            //Check if the row already exists in the corresponding group
            $rowExists = in_array($row, $groupedInputMatch[$value]['rows'] ?? []);

            //If the row does not exist yet, add it
            if (! $rowExists) {
                if (isset($groupedInputMatch[$value])) {
                    $groupedInputMatch[$value]['rows'][] = $row;
                } else {
                    $groupedInputMatch[$value] = ['rows' => [$row]];
                }
            }

            // Count occurrences of each row
            $rowsCount = ($groupedInputMatch[$value]['occurrences'][$row] ?? 0) + 1;
            $groupedInputMatch[$value]['occurrences'][$row] = $rowsCount;
        }

        // Return view with grouped input matches, count, and rows count
        return view('hardware', ['groupedInputMatch' => $groupedInputMatch, 'count' => $count, 'installation' => $installation, 'searchTerm' => $searchTerm]);
    }
}
