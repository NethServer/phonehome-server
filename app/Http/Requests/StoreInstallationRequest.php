<?php

//
// Copyright (C) 2022 Nethesis S.r.l.
// SPDX-License-Identifier: AGPL-3.0-or-later
//

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Validator as JsonSchemaValidator;

class StoreInstallationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            '$schema' => 'required|starts_with:https://schema.nethserver.org/',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // if $schema has been validated, validate against it.
            if ($validator->safe()->has('$schema')) {
                /** @var JsonSchemaValidator */
                $jsonValidator = app()->make(JsonSchemaValidator::class);
                $jsonValidated = $jsonValidator->validate(
                    json_decode($this->getContent()),
                    $validator->safe()['$schema']
                );
                // fill errorbag in case format errors are present.
                if ($jsonValidated->hasError()) {
                    $formatter = new ErrorFormatter();
                    $errors = $formatter->formatKeyed($jsonValidated->error());
                    foreach ($errors as $key => $value) {
                        $validator->errors()->add($key, $value[0]);
                    }
                }
            }
        });
    }
}
