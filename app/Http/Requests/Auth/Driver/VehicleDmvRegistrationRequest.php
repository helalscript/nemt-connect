<?php

namespace App\Http\Requests\Auth\Driver;

use Illuminate\Foundation\Http\FormRequest;

class VehicleDmvRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dmv_registration' => 'required|array',
            'dmv_registration.license_plate_number' => 'required|string|max:20',
            'dmv_registration.license_plate_category_id' => 'required|integer|in:1,2,3,4,5',
            'dmv_registration.state_code' => 'required|string|size:2',
            'dmv_registration.document' => 'nullable|file|mimes:pdf|max:5120',
            'dmv_registration.effective_date' => 'required|date',
            'dmv_registration.expiration_date' => 'required|date|after_or_equal:dmv_registration.effective_date',
        ];
    }
}

