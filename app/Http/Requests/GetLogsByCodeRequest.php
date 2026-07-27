<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetLogsByCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|integer|min:100|max:599',
            'limit' => 'nullable|integer|min:1|max:500',
        ];
    }
}
