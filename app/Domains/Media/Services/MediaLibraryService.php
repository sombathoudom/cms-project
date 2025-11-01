<?php

namespace App\Domains\Media\Services;

use App\Domains\Content\Models\Content;
use App\Domains\Media\Models\Media;
use App\Domains\Media\Models\MediaUsage;
use App\Domains\Security\Services\AuditLogger;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MediaLibraryService
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function storeUpload(UploadedFile $file, User $user, ?string $disk = null): Media
    {
        $diskName = $disk ?? config('filesystems.default', 'public');
        $directory = 'media/'.now()->format('Y/m');
        $storedPath = $file->store($directory, ['disk' => $diskName]);

        $dimensions = $this->extractImageDimensions($file);

        return DB::transaction(function () use ($file, $user, $diskName, $storedPath, $dimensions): Media {
            $media = Media::query()->create([
                'tenant_id' => $user->tenant_id,
                'disk' => $diskName,
                'path' => $storedPath,
                'original_name' => $file->getClientOriginalName() ?: $file->hashName(),
                'mime_type' => $file->getMimeType() ?: $file->getClientMimeType() ?: 'application/octet-stream',
                'size' => (int) $file->getSize(),
                'width' => $dimensions['width'],
                'height' => $dimensions['height'],
                'variants' => [],
                'uploaded_by' => $user->getAuthIdentifier(),
            ]);

            $this->auditLogger->log('media.uploaded', $media, [
                'size' => $media->size,
                'mime_type' => $media->mime_type,
            ]);

            Log::info('media.uploaded', [
                'media_id' => $media->getKey(),
                'tenant_id' => $media->tenant_id,
                'actor_id' => $user->getAuthIdentifier(),
                'correlation_id' => request()?->attributes->get('X-Correlation-ID'),
            ]);

            return $media;
        });
    }

    public function attachToContent(Content $content, Media $media, User $actor, string $altText, array $meta = []): MediaUsage
    {
        return DB::transaction(function () use ($content, $media, $actor, $altText, $meta): MediaUsage {
            $usage = MediaUsage::query()->create([
                'media_id' => $media->getKey(),
                'usable_type' => $content->getMorphClass(),
                'usable_id' => $content->getKey(),
                'context' => 'body',
                'alt_text' => $altText,
                'position' => (int) ($meta['position'] ?? 0),
                'meta' => $meta,
            ]);

            $this->auditLogger->log('media.embedded', $content, [
                'media_id' => $media->getKey(),
                'alt_text_present' => $altText !== '',
            ]);

            Log::info('media.embedded', [
                'content_id' => $content->getKey(),
                'media_id' => $media->getKey(),
                'tenant_id' => $content->tenant_id,
                'actor_id' => $actor->getAuthIdentifier(),
                'correlation_id' => request()?->attributes->get('X-Correlation-ID'),
            ]);

            return $usage;
        });
    }

    public function renderResponsiveMarkup(Media $media, string $altText, array $options = []): string
    {
        $escapedAlt = e($altText);
        $ratio = $this->calculateAspectRatio($media->width, $media->height);
        $loading = $options['loading'] ?? 'lazy';
        $class = $options['class'] ?? 'prose-img rounded-md shadow-sm';
        $width = $media->width ?? 0;
        $height = $media->height ?? 0;

        $style = $ratio ? 'style="aspect-ratio: '.$ratio.'"' : '';

        return sprintf(
            '<figure data-media-id="%s" class="media-embed"><img src="%s" alt="%s" loading="%s" class="%s" width="%d" height="%d" %s><figcaption class="sr-only">%s</figcaption></figure>',
            e($media->getKey()),
            e($media->url),
            $escapedAlt,
            e($loading),
            e($class),
            $width,
            $height,
            $style,
            $escapedAlt
        );
    }

    /**
     * @return array{width: ?int, height: ?int}
     */
    private function extractImageDimensions(UploadedFile $file): array
    {
        if (! Str::startsWith($file->getMimeType() ?? '', 'image/')) {
            return ['width' => null, 'height' => null];
        }

        $path = $file->getRealPath();

        if (! $path) {
            return ['width' => null, 'height' => null];
        }

        [$width, $height] = getimagesize($path) ?: [null, null];

        return [
            'width' => $width ? (int) $width : null,
            'height' => $height ? (int) $height : null,
        ];
    }

    private function calculateAspectRatio(?int $width, ?int $height): ?string
    {
        if (! $width || ! $height || $width === 0) {
            return null;
        }

        $gcd = $this->greatestCommonDivisor($width, $height);

        return ($width / $gcd).'/'.($height / $gcd);
    }

    private function greatestCommonDivisor(int $a, int $b): int
    {
        while ($b !== 0) {
            [$a, $b] = [$b, $a % $b];
        }

        return abs($a);
    }
}
