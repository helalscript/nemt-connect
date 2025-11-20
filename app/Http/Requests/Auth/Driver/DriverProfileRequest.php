<?php

namespace App\Http\Requests\Auth\Driver;

use Illuminate\Foundation\Http\FormRequest;

class DriverProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'working_area' => 'required|string|max:255',
            'working_area_lat' => 'required|numeric',
            'working_area_lng' => 'required|numeric',
            'working_area_radius' => 'required|integer|min:1|max:100',

            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email|unique:users,email,NULL,id,deleted_at,NULL',
            'social_security_number' => 'required|string|size:4|unique:driver_profiles,social_security_number,NULL,id,deleted_at,NULL',
            'gender' => 'required|string|in:male,female,others',
            'birth_date' => 'required|date|before_or_equal:today',
            'phone' => 'required|numeric|max_digits:20|unique:users,phone,NULL,id,deleted_at,NULL',
        ];
    }
}

