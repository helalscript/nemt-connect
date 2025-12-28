<?php

namespace App\Http\Requests\Auth\Driver;

use Illuminate\Foundation\Http\FormRequest;

class VehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vin' => 'required|string|size:17|unique:vehicles,vin',
            'fleet_number' => 'nullable|string|max:50',
            'production_year' => 'required|integer|min:1900|max:' . date('Y'),
            'type_id' => 'required|integer|in:1,2,3',
            'make' => 'required|string|max:100',
            'model' => 'required_with:make|string|max:100',
            'color' => 'required|string|max:50',
            'seat_number' => 'nullable|integer|min:1|max:20',
            'vehicle_status_id' => 'required|integer|in:1,2,3',
        ];
    }
}

