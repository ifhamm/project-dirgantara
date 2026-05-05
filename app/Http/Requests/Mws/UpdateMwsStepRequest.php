<?php

namespace App\Http\Requests\Mws;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMwsStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mws_part_id' => ['required', 'integer'],
            'field' => ['required', Rule::in(['description', 'plan_man', 'plan_hours'])],
            'value' => ['required', 'string'],
        ];
    }
}
