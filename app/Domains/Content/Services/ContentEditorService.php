<?php

namespace App\Domains\Content\Services;

use App\Domains\Content\Models\Content;
use App\Domains\Content\Models\ContentPreviewLink;
use App\Domains\Content\Models\ContentRevision;
use App\Domains\Security\Services\AuditLogger;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ContentEditorService
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function autoSave(Content $content, array $payload, User $editor): Content
    {
        $allowed = ['title', 'excerpt', 'body', 'settings_snapshot'];
        $updates = Arr::only($payload, $allowed);

        if ($updates === []) {
            return $content;
        }

        $changes = collect($updates)
            ->mapWithKeys(fn ($value, string $key) => [$key => $value])
            ->filter(fn ($value, string $key) => $content->getAttribute($key) !== $value)
            ->toArray();

        if ($changes === []) {
            return $content;
        }

        return DB::transaction(function () use ($content, $changes, $updates, $editor) {
            ContentRevision::query()->create([
                'content_id' => $content->getKey(),
                'editor_id' => $editor->getAuthIdentifier(),
                'diff' => $changes,
                'body' => $updates['body'] ?? $content->body,
            ]);

            $content->fill($updates);
            $content->save();

            $this->auditLogger->log('content.autosaved', $content, [
                'changes' => array_keys($changes),
                'editor_id' => $editor->getAuthIdentifier(),
            ]);

            Log::info('content.autosave.completed', [
                'content_id' => $content->getKey(),
                'editor_id' => $editor->getAuthIdentifier(),
                'tenant_id' => $content->tenant_id,
                'correlation_id' => optional(request())->attributes->get('X-Correlation-ID'),
            ]);

            return $content->fresh();
        });
    }

    /**
     * @return array{url: string, expires_at: \Illuminate\Support\Carbon}
     */
    public function createPreviewLink(Content $content, User $user, int $ttlMinutes = 30): array
    {
        if ($ttlMinutes <= 0) {
            throw new InvalidArgumentException('TTL must be greater than zero.');
        }

        $token = (string) Str::uuid();
        $expiresAt = now()->addMinutes($ttlMinutes);

        DB::transaction(function () use ($content, $user, $token, $expiresAt): void {
            ContentPreviewLink::query()->create([
                'content_id' => $content->getKey(),
                'token' => $token,
                'expires_at' => $expiresAt,
                'created_by' => $user->getAuthIdentifier(),
            ]);

            $this->auditLogger->log('content.preview_link.generated', $content, [
                'expires_at' => $expiresAt->toIso8601String(),
                'actor_id' => $user->getAuthIdentifier(),
            ]);

            Log::info('content.preview_link.generated', [
                'content_id' => $content->getKey(),
                'actor_id' => $user->getAuthIdentifier(),
                'expires_at' => $expiresAt->toIso8601String(),
                'correlation_id' => optional(request())->attributes->get('X-Correlation-ID'),
            ]);
        });

        $url = URL::temporarySignedRoute(
            'content.preview',
            $expiresAt,
            [
                'content' => $content->getKey(),
                'token' => $token,
            ]
        );

        return [
            'url' => $url,
            'expires_at' => $expiresAt,
        ];
    }
}
