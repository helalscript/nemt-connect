<?php

namespace App\Http\Requests\Auth\Driver;

use Illuminate\Foundation\Http\FormRequest;

class DriverExtraLicenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'extra_commercial_licenses'                 => 'array',
            'extra_commercial_licenses.license_number'  => 'required|string|max:20',
            'extra_commercial_licenses.type_id'         => 'required|integer|in:1,2,3,4,5,6',
            'extra_commercial_licenses.document'        => 'nullable|file|mimes:pdf|max:5120',
            'extra_commercial_licenses.effective_date'  => 'required|date',
            'extra_commercial_licenses.expiration_date' => 'required|date|after_or_equal:extra_commercial_licenses.effective_date',
        ];
    }

    public function messages(): array
    {
        return [
            'extra_commercial_licenses.expiration_date.after_or_equal' => 'Extra license expiration date must be after or equal to effective date.',
        ];
    }
}

