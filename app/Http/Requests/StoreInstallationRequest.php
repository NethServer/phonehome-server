<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInstallationRequest extends FormRequest
{
    /**
     * Rules that are applied to the store request for the Installation
     */
    public static $rules = [
        'uuid' => 'required|uuid',
        'release' => [
            'required',
            'regex:/^\d+\.\d+\.?\d*$/m' // uses preg_match
        ],
        'type' => 'nullable|in:community,enterprise,subscription'
    ];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return self::$rules;
    }
}
