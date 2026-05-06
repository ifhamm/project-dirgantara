<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role, ['admin', 'superadmin']);
    }

    public function rules(): array
    {
        return [
            'customer'      => ['required', 'string', 'max:255'],
            'contract_no'   => ['nullable', 'string', 'max:255'],
            'aircraft_type' => ['required', 'string', 'max:100'],
            'aircraft_reg'  => ['required', 'string', 'max:100'],
            'description'   => ['nullable', 'string'],
            'start_date'    => ['nullable', 'date'],
            'finish_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
            'work_days'     => ['nullable', 'integer', 'min:1'],

            // Dock phases (opsional saat create manual)
            'phases'                      => ['nullable', 'array'],
            'phases.predock.start_date'   => ['nullable', 'date'],
            'phases.predock.finish_date'  => ['nullable', 'date', 'after_or_equal:phases.predock.start_date'],
            'phases.predock.work_days'    => ['nullable', 'integer', 'min:1'],
            'phases.indock.start_date'    => ['nullable', 'date'],
            'phases.indock.finish_date'   => ['nullable', 'date', 'after_or_equal:phases.indock.start_date'],
            'phases.indock.work_days'     => ['nullable', 'integer', 'min:1'],
            'phases.postdock.start_date'  => ['nullable', 'date'],
            'phases.postdock.finish_date' => ['nullable', 'date', 'after_or_equal:phases.postdock.start_date'],
            'phases.postdock.work_days'   => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer.required'       => 'Customer wajib diisi.',
            'aircraft_type.required'  => 'Aircraft Type wajib diisi.',
            'aircraft_reg.required'   => 'Aircraft Reg wajib diisi.',
            'finish_date.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
        ];
    }
}