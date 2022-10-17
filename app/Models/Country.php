<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;

    /**
     * Get all of the installations for the Country
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function installations(): HasMany
    {
        return $this->hasMany(Installation::class);
    }
}
