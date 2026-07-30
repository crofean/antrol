<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RunCommandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'dry_run' => 'nullable|boolean',
            'mjkn' => 'nullable|boolean',
            'all' => 'nullable|boolean',
        ];
    }
}
