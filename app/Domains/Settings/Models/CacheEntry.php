<?php

namespace App\Domains\Settings\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CacheEntry extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'key',
        'value',
        'ttl',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
