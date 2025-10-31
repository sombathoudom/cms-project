<?php

namespace App\Domains\Content\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoMeta extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'meta_title',
        'meta_description',
        'canonical_url',
        'open_graph',
    ];

    protected $casts = [
        'open_graph' => 'array',
    ];

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }
}
