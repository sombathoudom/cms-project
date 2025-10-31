<?php

namespace App\Domains\Content\Models;

use App\Domains\Media\Models\Media;
use App\Domains\Taxonomy\Models\Category;
use App\Domains\Taxonomy\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Content extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'contents';

    protected $fillable = [
        'tenant_id',
        'type',
        'title',
        'slug',
        'excerpt',
        'body',
        'featured_media_id',
        'status',
        'publish_at',
        'author_id',
        'reviewer_id',
        'settings_snapshot',
        'published_at',
        'category_id',
    ];

    protected $casts = [
        'publish_at' => 'datetime',
        'published_at' => 'datetime',
        'settings_snapshot' => 'array',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(ContentRevision::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'content_tag');
    }

    public function seoMeta(): MorphOne
    {
        return $this->morphOne(\App\Domains\Content\Models\SeoMeta::class, 'seoable');
    }
}
