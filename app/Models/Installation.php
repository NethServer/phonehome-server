<?php

//
// Copyright (C) 2022 Nethesis S.r.l.
// SPDX-License-Identifier: AGPL-3.0-or-later
//

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
     * Filters clusters that are running NethServer 8.
     *
     * @param Builder $query
     * @return void
     */
    public function scopeNethserverEight(Builder $query): void
    {
        $query->whereJsonContains('data->installation', 'nethserver')
            ->whereJsonContainsKey('data->facts->nodes');
    }

    /**
     * Filters installations that are running NethSecurity 8.
     *
     * @param Builder $query
     * @return void
     */
    public function scopeNethsecurityEight(Builder $query): void
    {
        $query->whereJsonContains('data->installation', 'nethsecurity');
    }

    /**
     * Filters entries that are alive in the last 2 days.
     *
     * @param Builder $query
     * @return void
     */
    public function scopeActive(Builder $query): void
    {
        $query->whereBetween('updated_at', [now()->subDays(2), now()]);
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
