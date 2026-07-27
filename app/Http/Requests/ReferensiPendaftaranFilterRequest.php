<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReferensiPendaftaranFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to' => 'nullable|date_format:Y-m-d',
            'no_rawat' => 'nullable|string',
            'no_booking' => 'nullable|string',
            'status' => 'nullable|string',
            'no_booking_list' => 'nullable|string',
        ];
    }
}
