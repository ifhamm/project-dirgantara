<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

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
            'name'     => ['required', 'string', 'max:255', 'unique:users,name,' . $userId],
            'nik'      => ['nullable', 'string', 'max:50', 'unique:users,nik,' . $userId],
            'role'     => ['required', 'string', 'in:mechanic,quality inspector,quality cvdr'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $userId],
            'password' => $this->isMethod('post') 
                ? ['required', 'confirmed', Password::defaults()] 
                : ['nullable', 'confirmed', Password::defaults()],
        ];
    }
}