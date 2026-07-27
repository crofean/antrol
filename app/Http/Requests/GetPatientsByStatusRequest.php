<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetPatientsByStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|string',
            'date' => 'nullable|date',
        ];
    }
}
