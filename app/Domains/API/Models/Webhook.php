<?php

namespace App\Domains\API\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'tenant_id',
        'url',
        'secret',
        'active',
        'headers',
        'last_attempt_at',
    ];

    protected $casts = [
        'headers' => 'array',
        'last_attempt_at' => 'datetime',
        'active' => 'bool',
    ];
}
