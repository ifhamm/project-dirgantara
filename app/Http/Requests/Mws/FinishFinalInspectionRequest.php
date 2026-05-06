<?php

namespace App\Http\Requests\Mws;

use Illuminate\Foundation\Http\FormRequest;

class FinishFinalInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status_s_us' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
