<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BatalAntreanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kodebooking' => 'nullable|string',
            'no_rawat' => 'nullable|string',
            'keterangan' => 'nullable|string',
        ];
    }
}
