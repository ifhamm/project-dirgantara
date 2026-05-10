<?php

namespace App\Http\Requests\TaskGroup;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role, ['admin', 'superadmin']);
    }

    public function rules(): array
    {
        return [
            'no'          => ['nullable', 'string', 'max:20'],
            'name'        => ['required', 'string', 'max:255'],
            'start_date'  => ['nullable', 'date'],
            'finish_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'work_days'   => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'              => 'Nama task group wajib diisi.',
            'finish_date.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
        ];
    }
}