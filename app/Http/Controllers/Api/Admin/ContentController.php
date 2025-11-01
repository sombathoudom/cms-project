<?php

namespace App\Http\Controllers\Api\Admin;

use App\Domains\Content\Models\Content;
use App\Domains\Content\Services\ContentEditorService;
use App\Domains\Media\Models\Media;
use App\Domains\Media\Services\MediaLibraryService;
use App\Http\Controllers\Controller;
use App\Http\Requests\ContentAutoSaveRequest;
use App\Http\Requests\ContentEmbedMediaRequest;
use App\Http\Resources\ContentResource;
use App\Http\Resources\MediaResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function __construct(
        private readonly ContentEditorService $contentEditorService,
        private readonly MediaLibraryService $mediaLibraryService,
    )
    {
    }

    public function autoSave(ContentAutoSaveRequest $request, Content $content): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return $this->errorResponse('ERR_UNAUTHENTICATED', 'Authentication required.', 401);
        }

        if ($user->cannot('update', $content)) {
            return $this->errorResponse('ERR_FORBIDDEN', 'You are not allowed to update this content.', 403);
        }

        $saved = $this->contentEditorService->autoSave($content, $request->validated(), $user)->load(['author']);

        return (new ContentResource($saved))->response();
    }

    public function previewLink(Request $request, Content $content): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return $this->errorResponse('ERR_UNAUTHENTICATED', 'Authentication required.', 401);
        }

        if ($user->cannot('update', $content)) {
            return $this->errorResponse('ERR_FORBIDDEN', 'You are not allowed to generate a preview for this content.', 403);
        }

        $link = $this->contentEditorService->createPreviewLink($content, $user);

        return response()->json([
            'data' => [
                'preview_url' => $link['url'],
                'expires_at' => $link['expires_at']->toIso8601String(),
            ],
        ]);
    }

    public function embedMedia(ContentEmbedMediaRequest $request, Content $content): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return $this->errorResponse('ERR_UNAUTHENTICATED', 'Authentication required.', 401);
        }

        $payload = $request->validated();

        $media = Media::query()
            ->forTenant($user->tenant_id)
            ->find($payload['media_id']);

        if (! $media) {
            return $this->errorResponse('ERR_NOT_FOUND', 'Media asset not found for this tenant.', 404);
        }

        $this->mediaLibraryService->attachToContent(
            $content,
            $media,
            $user,
            (string) ($payload['alt_text'] ?? ''),
            ['position' => $payload['position'] ?? 0]
        );

        $markup = $this->mediaLibraryService->renderResponsiveMarkup(
            $media,
            (string) ($payload['alt_text'] ?? ''),
            ['position' => $payload['position'] ?? 0]
        );

        return response()->json([
            'data' => [
                'markup' => $markup,
                'media' => MediaResource::make($media),
            ],
        ]);
    }
}
