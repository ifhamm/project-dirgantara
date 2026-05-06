<?php

namespace App\Http\Requests\Mws;

use Illuminate\Foundation\Http\FormRequest;

class BulkDeleteMwsStepsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'step_nos' => ['required', 'array', 'min:1'],
            'step_nos.*' => ['integer', 'min:1'],
        ];
    }
}
