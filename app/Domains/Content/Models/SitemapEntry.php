<?php

namespace App\Domains\Content\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SitemapEntry extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'url',
        'changefreq',
        'priority',
        'last_modified_at',
    ];

    protected $casts = [
        'last_modified_at' => 'datetime',
        'priority' => 'float',
    ];
}
