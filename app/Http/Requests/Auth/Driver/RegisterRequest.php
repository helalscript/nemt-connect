<?php

namespace App\Http\Requests\Auth\Driver;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'working_area' => 'required|string|max:255',
            'working_area_lat' => 'required|numeric',
            'working_area_lng' => 'required|numeric',
            'working_area_radius' => 'required|integer|min:1|max:100',

            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
            'social_security_number' => 'required|string|max:4|min:4|unique:driver_profiles,social_security_number,NULL,id,deleted_at,NULL',
            'gender' => 'required|string|in:male,female,others',
            'birth_date' => 'required|date|before_or_equal:today',
            'phone' => 'required|numeric|max_digits:20|unique:users,phone,NULL,id,deleted_at,NULL',

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

            'extra_commercial_licenses' => 'array',
            'extra_commercial_licenses.license_number' => 'required|string|max:20',
            'extra_commercial_licenses.type_id' => 'required|integer|in:1,2,3,4,5,6',
            'extra_commercial_licenses.document' => 'nullable|file|mimes:pdf|max:5120',
            'extra_commercial_licenses.effective_date' => 'required|date',
            'extra_commercial_licenses.expiration_date' => 'required|date|after_or_equal:extra_commercial_licenses.effective_date',

            'background_check' => 'nullable|array',
            'background_check.document' => 'nullable|file|mimes:pdf|max:5120',
            'background_check.effective_date' => 'nullable|date|required_with:background_check',
            'background_check.expiration_date' => 'nullable|date|required_with:background_check|after_or_equal:background_check.effective_date',

            'vin' => 'required|string|max:50',
            'fleet_number' => 'nullable|string|max:50',
            'production_year' => 'required|integer|min:1900|max:'.date('Y'),
            'type_id' => 'required|integer',
            'make' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'color' => 'required|string|max:50',
            'seat_number' => 'nullable|integer|min:1|max:100',
            'vehicle_status_id' => 'required|integer',

            'dmv_registration' => 'required|array',
            'dmv_registration.license_plate_number' => 'required|string|max:20',
            'dmv_registration.license_plate_category_id' => 'required|integer',
            'dmv_registration.state_code' => 'required|string|size:2',
            'dmv_registration.document' => 'nullable|file|mimes:pdf|max:5120',
            'dmv_registration.effective_date' => 'required|date',
            'dmv_registration.expiration_date' => 'required|date|after_or_equal:dmv_registration.effective_date',

            'extra_commercial_license' => 'nullable|array',
            'extra_commercial_license.license_number' => 'required_with:extra_commercial_license|string|max:50',
            'extra_commercial_license.type_id' => 'required_with:extra_commercial_license|integer',
            'extra_commercial_license.document' => 'nullable|file|mimes:pdf|max:5120',
            'extra_commercial_license.effective_date' => 'required_with:extra_commercial_license|date',
            'extra_commercial_license.expiration_date' => 'required_with:extra_commercial_license|date|after_or_equal:extra_commercial_license.effective_date',

            'insurance' => 'required|array',
            'insurance.policy_number' => 'required|string|max:100',
            'insurance.insurer_name' => 'required|string|max:150',
            'insurance.document' => 'nullable|file|mimes:pdf|max:5120',
            'insurance.effective_date' => 'required|date',
            'insurance.expiration_date' => 'required|date|after_or_equal:insurance.effective_date',

            'inspection' => 'required|array',
            'inspection.document' => 'nullable|file|mimes:pdf|max:5120',
            'inspection.effective_date' => 'required|date',
            'inspection.expiration_date' => 'required|date|after_or_equal:inspection.effective_date',
        ];
    }

    public function messages(): array
    {
        return [
            'dmv_license.expiration_date.after_or_equal' => 'DMV license expiration date must be after or equal to effective date.',
            'extra_commercial_licenses.*.expiration_date.after_or_equal' => 'Extra license expiration date must be after or equal to effective date.',
            'background_check.expiration_date.after_or_equal' => 'Background check expiration date must be after or equal to effective date.',
        ];
    }
}
