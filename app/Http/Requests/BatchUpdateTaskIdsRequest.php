<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BatchUpdateTaskIdsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'updates' => 'required|array',
            'updates.*.kodebooking' => 'required|string',
            'updates.*.taskid' => 'required|integer|in:1,2,3,4,5,6,7,99',
            'updates.*.waktu' => 'nullable|string',
        ];
    }
}
