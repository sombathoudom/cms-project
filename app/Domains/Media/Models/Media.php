<?php

namespace App\Domains\Media\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
        'width',
        'height',
        'variants',
        'uploaded_by',
        'replaced_at',
    ];

    protected $casts = [
        'variants' => 'array',
        'replaced_at' => 'datetime',
    ];

    protected $appends = [
        'url',
    ];

    public function scopeForTenant(Builder $query, ?string $tenantId): Builder
    {
        if ($tenantId === null) {
            return $query;
        }

        return $query->where('tenant_id', $tenantId);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $inner) use ($term): void {
            $likeTerm = '%'.$term.'%';

            $inner->where('original_name', 'like', $likeTerm)
                ->orWhere('mime_type', 'like', $likeTerm);
        });
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(MediaUsage::class);
    }

    public function contents(): MorphToMany
    {
        return $this->morphedByMany(\App\Domains\Content\Models\Content::class, 'usable', 'media_usages');
    }
}
