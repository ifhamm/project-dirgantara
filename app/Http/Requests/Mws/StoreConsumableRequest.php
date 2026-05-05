<?php

namespace App\Http\Requests\Mws;

use Illuminate\Foundation\Http\FormRequest;

class StoreConsumableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'identification' => ['nullable', 'string', 'max:255'],
            'quantity' => ['nullable', 'string', 'max:50'],
        ];
    }
}
