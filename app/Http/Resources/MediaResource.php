<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Domains\Media\Models\Media
 */
class MediaResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'original_name' => $this->resource->original_name,
            'mime_type' => $this->resource->mime_type,
            'size' => $this->resource->size,
            'width' => $this->resource->width,
            'height' => $this->resource->height,
            'url' => $this->resource->url,
            'uploaded_at' => optional($this->resource->created_at)->toIso8601String(),
        ];
    }
}
