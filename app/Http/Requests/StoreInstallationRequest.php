<?php

#
# Copyright (C) 2022 Nethesis S.r.l.
# SPDX-License-Identifier: AGPL-3.0-or-later
#

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInstallationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'uuid' => 'required|uuid',
            'release' => [
                'required',
                'regex:/^\d+\.\d+\.?\d*$/m' // uses preg_match
            ],
            'type' => 'nullable|in:community,enterprise,subscription'
        ];
    }
}
