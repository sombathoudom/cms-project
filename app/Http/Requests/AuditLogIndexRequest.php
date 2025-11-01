<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AuditLogIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('audit.view') ?? false;
    }

    protected function failedAuthorization(): void
    {
        if (! $this->user()) {
            throw new HttpResponseException(response()->json([
                'error' => [
                    'code' => 'ERR_UNAUTHENTICATED',
                    'message' => 'Authentication required.',
                ],
            ], 401));
        }

        throw new HttpResponseException(response()->json([
            'error' => [
                'code' => 'ERR_FORBIDDEN',
                'message' => 'You do not have permission to view audit logs.',
            ],
        ], 403));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'action' => ['sometimes', 'string', 'max:191'],
            'actor_id' => ['sometimes', 'uuid'],
            'target_type' => ['sometimes', 'string', 'max:191'],
            'target_id' => ['sometimes', 'uuid'],
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
