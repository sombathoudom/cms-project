<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class MediaUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        return $user->can('media.create');
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:10240', 'mimetypes:image/jpeg,image/png,image/gif,video/mp4,application/pdf'],
            'alt_text' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(response()->json([
            'error' => [
                'code' => 'ERR_FORBIDDEN',
                'message' => 'You do not have permission to upload media.',
            ],
        ], 403));
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'error' => [
                'code' => 'ERR_VALIDATION',
                'message' => $validator->errors()->first() ?? 'Invalid media upload payload.',
                'details' => $validator->errors()->messages(),
            ],
        ], 422));
    }
}
