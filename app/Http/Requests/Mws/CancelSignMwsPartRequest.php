<?php

namespace App\Http\Requests\Mws;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CancelSignMwsPartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['prepared', 'approved', 'verified'])],
        ];
    }
}
