<?php

namespace App\Http\Controllers\Api\Admin;

use App\Domains\Content\Models\Content;
use App\Domains\Content\Services\ContentEditorService;
use App\Http\Controllers\Controller;
use App\Http\Requests\ContentAutoSaveRequest;
use App\Http\Resources\ContentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function __construct(private readonly ContentEditorService $contentEditorService)
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
}
