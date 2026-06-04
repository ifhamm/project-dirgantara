<?php

namespace App\Http\Requests\Mws;

use Illuminate\Foundation\Http\FormRequest;

class StoreMwsPartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Required fields
            'title' => ['required', 'string', 'max:255'],
            'job_type' => ['required', 'string', 'max:100'],
            'customer_name' => ['required', 'string', 'max:255'],
            'part_number' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'shop_area' => ['required', 'string', 'max:100'],
            'wbs_no' => ['nullable', 'string', 'max:255'],
            'ref' => ['nullable', 'string', 'max:255'],
            'worksheet_no' => ['nullable', 'string', 'max:255'],
            
            // Optional fields
            'ref_logistic_ppc' => ['nullable', 'string', 'max:255'],
            'mdr_doc_defect' => ['nullable', 'string', 'max:255'],
            'capability' => ['nullable', 'string', 'max:255'],
            'remark_mws' => ['nullable', 'string'],
            'test_result' => ['nullable', 'string'],
            'ac_type' => ['nullable', 'string', 'max:255'],
            'revision' => ['nullable', 'string', 'max:50'],
            'zone' => ['nullable', 'string', 'max:100'],
            'task_id' => ['required', 'exists:tasks,id'],
        ];
    }
}
