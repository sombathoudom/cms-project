<?php

namespace App\Http\Controllers\Api\Admin;

use App\Domains\Security\Models\AuditLog;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuditLogIndexRequest;
use App\Http\Resources\AuditLogResource;
use Illuminate\Http\JsonResponse;

class AuditLogController extends Controller
{
    public function index(AuditLogIndexRequest $request): JsonResponse
    {
        $actor = $request->user();

        if (! $actor) {
            return $this->errorResponse('ERR_UNAUTHENTICATED', 'Authentication required.', 401);
        }

        $filters = $request->validated();

        $query = AuditLog::query()
            ->with(['actor'])
            ->orderByDesc('created_at');

        if ($actor->tenant_id) {
            $query->where('tenant_id', $actor->tenant_id);
        }

        if (! empty($filters['action'])) {
            $query->where('action', 'like', $filters['action'].'%');
        }

        if (! empty($filters['actor_id'])) {
            $query->where('actor_id', $filters['actor_id']);
        }

        if (! empty($filters['target_type'])) {
            $query->where('target_type', $filters['target_type']);
        }

        if (! empty($filters['target_id'])) {
            $query->where('target_id', $filters['target_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $perPage = (int) ($filters['per_page'] ?? 25);

        /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator */
        $paginator = $query->paginate($perPage)->appends($filters);

        return AuditLogResource::collection($paginator)
            ->additional([
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                ],
            ])
            ->response();
    }
}
