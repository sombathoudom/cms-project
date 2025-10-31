<?php

namespace App\Domains\Content\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentSlugHistory extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'content_id',
        'slug',
    ];

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }
}
