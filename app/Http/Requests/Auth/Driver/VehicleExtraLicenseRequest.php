<?php

namespace App\Http\Requests\Auth\Driver;

use Illuminate\Foundation\Http\FormRequest;

class VehicleExtraLicenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'extra_commercial_license'                 => 'nullable|array',
            'extra_commercial_license.license_number'  => 'required_with:extra_commercial_license|string|max:50',
            'extra_commercial_license.type_id'         => 'required_with:extra_commercial_license|integer|in:1,2,3',
            'extra_commercial_license.document'        => 'nullable|file|mimes:pdf|max:5120',
            'extra_commercial_license.effective_date'  => 'required_with:extra_commercial_license|date',
            'extra_commercial_license.expiration_date' => 'required_with:extra_commercial_license|date|after_or_equal:extra_commercial_license.effective_date',
        ];
    }
}

