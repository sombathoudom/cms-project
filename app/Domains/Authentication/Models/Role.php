<?php

namespace App\Domains\Authentication\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'guard_name',
        'description',
        'is_default',
        'tenant_id',
    ];

    protected $casts = [
        'is_default' => 'bool',
    ];
}
