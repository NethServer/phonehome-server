<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PciDevice extends Model
{
    /**
     * Returns the installation associated with the PCI device.
     */
    public function installation(): BelongsTo
    {
        return $this->belongsTo(Installation::class);
    }
}
