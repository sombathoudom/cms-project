<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ContentEmbedMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        $content = $this->route('content');

        if (! $content) {
            return false;
        }

        return $user->can('update', $content);
    }

    public function rules(): array
    {
        return [
            'media_id' => ['required', 'uuid', 'exists:media,id'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(response()->json([
            'error' => [
                'code' => 'ERR_FORBIDDEN',
                'message' => 'You are not allowed to update this content.',
            ],
        ], 403));
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'error' => [
                'code' => 'ERR_VALIDATION',
                'message' => $validator->errors()->first() ?? 'Invalid media embed parameters.',
                'details' => $validator->errors()->messages(),
            ],
        ], 422));
    }
}
