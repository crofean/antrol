<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetPatientsByPolyclinicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kd_poli' => 'required|string',
            'date' => 'nullable|date',
        ];
    }
}
