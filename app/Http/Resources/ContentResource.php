<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domains\Content\Models\Content */
class ContentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'type' => $this->type,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'body' => $this->body,
            'status' => $this->status,
            'publish_at' => optional($this->publish_at)?->toIso8601String(),
            'published_at' => optional($this->published_at)?->toIso8601String(),
            'category_id' => $this->category_id,
            'author' => $this->whenLoaded('author', fn () => [
                'id' => $this->author?->getKey(),
                'name' => $this->author?->name,
            ]),
            'updated_at' => optional($this->updated_at)?->toIso8601String(),
            'created_at' => optional($this->created_at)?->toIso8601String(),
        ];
    }
}
