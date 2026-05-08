<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user');
        $userId = is_object($user) ? $user->id : $user;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('users', 'name')->ignore($userId)
            ],
            'nik' => [
                'nullable',
                'string',
                'max:50',
                \Illuminate\Validation\Rule::unique('users', 'nik')->ignore($userId)
            ],
            'role' => [
                'required',
                'string',
                'in:admin,mechanic,quality1,quality2'
            ],

            'email' => [
                'exclude_unless:role,admin',
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($userId)
            ],

            'password' => [
                'exclude_unless:role,admin',
                'nullable',
                'min:8'
            ],
        ];
    }
}
