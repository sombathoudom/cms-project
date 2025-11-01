<?php

namespace App\Http\Controllers\Api\Admin;

use App\Domains\Media\Models\Media;
use App\Domains\Media\Services\MediaLibraryService;
use App\Http\Controllers\Controller;
use App\Http\Requests\MediaIndexRequest;
use App\Http\Requests\MediaUploadRequest;
use App\Http\Resources\MediaResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class MediaController extends Controller
{
    public function __construct(private readonly MediaLibraryService $mediaLibraryService)
    {
    }

    public function index(MediaIndexRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return $this->errorResponse('ERR_UNAUTHENTICATED', 'Authentication required.', 401);
        }

        $filters = $request->validated();

        $query = Media::query()
            ->forTenant($user->tenant_id)
            ->search($filters['search'] ?? null);

        if (! empty($filters['type'])) {
            $query->where('mime_type', 'like', $filters['type'].'%');
        }

        $perPage = (int) ($filters['per_page'] ?? 15);

        $paginator = $query
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->appends($filters);

        Log::info('media.index.fetched', [
            'actor_id' => $user->getAuthIdentifier(),
            'tenant_id' => $user->tenant_id,
            'result_count' => $paginator->count(),
            'correlation_id' => $request->attributes->get('X-Correlation-ID'),
        ]);

        return MediaResource::collection($paginator)
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

    public function store(MediaUploadRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return $this->errorResponse('ERR_UNAUTHENTICATED', 'Authentication required.', 401);
        }

        $file = $request->file('file');

        if (! $file) {
            return $this->errorResponse('ERR_NO_FILE', 'Media file is required.', 422);
        }

        $media = $this->mediaLibraryService->storeUpload($file, $user);

        Log::info('media.store.success', [
            'actor_id' => $user->getAuthIdentifier(),
            'media_id' => $media->getKey(),
            'tenant_id' => $user->tenant_id,
            'correlation_id' => $request->attributes->get('X-Correlation-ID'),
        ]);

        return (new MediaResource($media))
            ->response()
            ->setStatusCode(201);
    }
}
