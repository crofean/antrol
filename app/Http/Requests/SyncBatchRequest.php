<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patients' => 'required|array|min:1',
            'patients.*.kodebooking' => 'required|string',
            'patients.*.no_rawat' => 'required|string',
        ];
    }
}
