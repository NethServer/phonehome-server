<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Version extends Model
{
    use HasFactory;

    /**
     * @inheritdoc
     */
    protected $fillable = [
        'tag'
    ];

    /**
     * Get all of the installations for the Version
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function installations(): HasMany
    {
        return $this->hasMany(Installation::class);
    }
}
