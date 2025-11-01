<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class MediaIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        return $user->can('media.view');
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:32'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'type' => $this->input('type') ?: null,
        ]);
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(response()->json([
            'error' => [
                'code' => 'ERR_FORBIDDEN',
                'message' => 'You do not have permission to view media assets.',
            ],
        ], 403));
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'error' => [
                'code' => 'ERR_VALIDATION',
                'message' => $validator->errors()->first() ?? 'Invalid media filter parameters.',
                'details' => $validator->errors()->messages(),
            ],
        ], 422));
    }
}
