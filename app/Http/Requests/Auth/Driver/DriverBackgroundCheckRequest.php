<?php

namespace App\Http\Requests\Auth\Driver;

use Illuminate\Foundation\Http\FormRequest;

class DriverBackgroundCheckRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'background_check'                 => 'nullable|array',
            'background_check.document'        => 'nullable|file|mimes:pdf|max:5120',
            'background_check.effective_date'  => 'nullable|date|required_with:background_check',
            'background_check.expiration_date' => 'nullable|date|required_with:background_check|after_or_equal:background_check.effective_date',
        ];
    }

    public function messages(): array
    {
        return [
            'background_check.expiration_date.after_or_equal' => 'Background check expiration date must be after or equal to effective date.',
        ];
    }
}

