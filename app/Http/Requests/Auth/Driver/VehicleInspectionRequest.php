<?php

namespace App\Http\Requests\Auth\Driver;

use Illuminate\Foundation\Http\FormRequest;

class VehicleInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'inspection'                 => 'required|array',
            'inspection.document'        => 'nullable|file|mimes:pdf|max:5120',
            'inspection.effective_date'  => 'required|date',
            'inspection.expiration_date' => 'required|date|after_or_equal:inspection.effective_date',
        ];
    }
}

