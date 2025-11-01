<?php

namespace App\Http\Controllers;

use App\Domains\Content\Models\Content;
use App\Domains\Content\Models\ContentPreviewLink;
use App\Domains\Security\Services\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ContentPreviewController extends Controller
{
    public function __invoke(Request $request, Content $content, string $token): View|RedirectResponse|Response
    {
        $link = ContentPreviewLink::query()
            ->where('content_id', $content->getKey())
            ->where('token', $token)
            ->latest('created_at')
            ->first();

        if (! $link || $link->hasExpired()) {
            abort(404, 'Preview link is no longer valid.');
        }

        app(AuditLogger::class)->log('content.preview.viewed', $content, [
            'preview_token' => $token,
        ]);

        Log::info('content.preview.viewed', [
            'content_id' => $content->getKey(),
            'token' => $token,
            'correlation_id' => optional($request)->attributes->get('X-Correlation-ID'),
        ]);

        return view('content.preview', [
            'content' => $content->loadMissing(['author', 'category', 'tags']),
        ]);
    }
}
