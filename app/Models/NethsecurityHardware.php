<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NethsecurityHardware extends Model
{
    use HasFactory;

    protected $table = 'nethsecurity_view';

    protected $fillable = [
        'product_name',
        'manufacturer',
        'processor',
        'vga_controller',
        'usb_controller',
        'pci_bridge',
        'stata_controller',
        'communication_controller',
        'scsi_controller',
        'ethernet',
    ];
}
