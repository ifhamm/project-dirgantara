<?php

namespace App\Http\Requests\Mws;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMwsPartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'part_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'serial_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'job_type' => ['sometimes', 'string', 'max:100'],
            'status' => ['sometimes', 'string', 'max:100'],
            'ref' => ['sometimes', 'nullable', 'string', 'max:255'],
            'acType' => ['sometimes', 'nullable', 'string', 'max:255'],
            'wbsNO' => ['sometimes', 'nullable', 'string', 'max:255'],
            'wroksheetNo' => ['sometimes', 'nullable', 'string', 'max:255'],
            'shopArea' => ['sometimes', 'nullable', 'string', 'max:100'],
            'revision' => ['sometimes', 'nullable', 'string', 'max:50'],
            'zone' => ['sometimes', 'nullable', 'string', 'max:100'],
            'start_date' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
