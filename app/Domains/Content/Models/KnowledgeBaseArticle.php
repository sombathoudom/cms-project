<?php

namespace App\Domains\Content\Models;

use Laravel\Scout\Searchable;

class KnowledgeBaseArticle extends Content
{
    use Searchable;

    public function toSearchableArray(): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'slug' => $this->slug,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->type = 'kb';
        });
    }
}
