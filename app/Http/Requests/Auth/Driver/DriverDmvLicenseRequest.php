<?php

namespace App\Http\Requests\Auth\Driver;

use Illuminate\Foundation\Http\FormRequest;

class DriverDmvLicenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dmv_license' => 'required|array',
            'dmv_license.license_number' => 'required|string|max:20',
            'dmv_license.state_code' => 'required|string|size:2',
            'dmv_license.license_class' => 'required|string|max:5',
            'dmv_license.endorsements' => 'array',
            'dmv_license.endorsements.*' => 'string|max:5',
            'dmv_license.restrictions' => 'array',
            'dmv_license.restrictions.*' => 'string|max:5',
            'dmv_license.document' => 'nullable|file|mimes:pdf|max:5120',
            'dmv_license.effective_date' => 'required|date',
            'dmv_license.expiration_date' => 'required|date|after_or_equal:dmv_license.effective_date',
        ];
    }

    public function messages(): array
    {
        return [
            'dmv_license.expiration_date.after_or_equal' => 'DMV license expiration date must be after or equal to effective date.',
        ];
    }
}

