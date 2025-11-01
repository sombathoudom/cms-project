<?php

namespace App\Domains\Media\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MediaUsage extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'media_id',
        'usable_type',
        'usable_id',
        'context',
    ];

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function usable(): MorphTo
    {
        return $this->morphTo();
    }
}
