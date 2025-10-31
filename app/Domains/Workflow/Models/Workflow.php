<?php

namespace App\Domains\Workflow\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workflow extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'name',
        'tenant_id',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public function steps(): HasMany
    {
        return $this->hasMany(WorkflowStep::class);
    }
}
