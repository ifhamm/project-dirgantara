<?php

namespace App\Http\Requests\Mws;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignMechanicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nik' => [
                'required',
                'string',
                Rule::exists('users', 'nik')->where('role', 'mechanic'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nik.exists' => 'User yang dipilih bukan akun mekanik.',
        ];
    }
}
