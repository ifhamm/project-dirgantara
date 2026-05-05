<?php

namespace App\Http\Requests\Mws;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConsumableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'identification' => ['sometimes', 'nullable', 'string', 'max:255'],
            'quantity' => ['sometimes', 'nullable', 'string', 'max:50'],
        ];
    }
}
