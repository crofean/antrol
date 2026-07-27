<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskIdFromDbRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kodebooking' => 'required|string',
            'taskid' => 'required|integer|in:3,4,5,6,7',
        ];
    }
}
