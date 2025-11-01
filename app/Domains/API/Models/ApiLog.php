<?php

namespace App\Domains\API\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'tenant_id',
        'method',
        'path',
        'status_code',
        'payload',
        'ip_address',
        'correlation_id',
        'logged_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'logged_at' => 'datetime',
    ];
}
