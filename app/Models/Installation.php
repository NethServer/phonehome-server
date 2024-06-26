<?php

//
// Copyright (C) 2022 Nethesis S.r.l.
// SPDX-License-Identifier: AGPL-3.0-or-later
//

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Installation extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'data->uuid',
        'data->installation',
        'data->facts',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    /**
     * Get the country associated with the Installation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Country, \App\Models\Installation>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
