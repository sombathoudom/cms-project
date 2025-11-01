<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContentAutoSaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string'],
            'body' => ['required', 'string'],
            'settings_snapshot' => ['sometimes', 'array'],
        ];
    }
}
