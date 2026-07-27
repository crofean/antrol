<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetPatientsByDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kd_dokter' => 'required|string',
            'date' => 'nullable|date',
        ];
    }
}
