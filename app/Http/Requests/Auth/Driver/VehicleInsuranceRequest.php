<?php

namespace App\Http\Requests\Auth\Driver;

use Illuminate\Foundation\Http\FormRequest;

class VehicleInsuranceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'insurance' => 'required|array',
            'insurance.policy_number' => 'required|string|max:100|unique:vehicle_insurances,policy_number',
            'insurance.insurer_name' => 'required|string|max:150',
            'insurance.document' => 'nullable|file|mimes:pdf|max:5120',
            'insurance.effective_date' => 'required|date',
            'insurance.expiration_date' => 'required|date|after_or_equal:insurance.effective_date',
        ];
    }
}

