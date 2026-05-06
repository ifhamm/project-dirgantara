<?php

namespace App\Http\Requests\Mws;

use Illuminate\Foundation\Http\FormRequest;

class StoreMwsWorkflowStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:255'],
        ];
    }
}
