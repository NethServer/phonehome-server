<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInstallationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'uuid' => 'required|uuid',
            'release' => [
                'required',
                'regex:/.*/m' // uses preg_match
            ],
            'type' => 'required|in:community,enterprise,subscription'
        ];
    }
}
