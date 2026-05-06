<?php

namespace App\Http\Requests\Mws;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMwsDatesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => ['sometimes', 'nullable', 'date'],
            'finish_date' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
